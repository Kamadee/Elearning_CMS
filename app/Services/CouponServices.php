<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CouponServices
{
    /**
     * Get active cart content for a customer.
     */
    public function getCartContent($customerId)
    {
        return Cart::select(
            'carts.id as id',
            'carts.customer_id as customer_id',
            'carts.course_id as course_id',
            'carts.quantity as quantity',
            'courses.title as course_title',
            DB::raw('(CASE WHEN sale_off_price IS NULL THEN original_price ELSE sale_off_price END) as price')
        )
            ->join('courses', 'courses.id', '=', 'carts.course_id')
            ->where('customer_id', $customerId)
            ->get();
    }

    /**
     * Validate coupon without changing any database records (Read-Only).
     */
    public function validateCoupon($couponCode, $customerId)
    {
        $codes = array_filter(array_map('trim', explode(',', $couponCode)));
        if (empty($codes)) {
            throw new \Exception('Mã giảm giá không hợp lệ.');
        }

        $coupons = [];
        $systemCount = 0;
        $courseCount = 0;

        foreach ($codes as $code) {
            $coupon = Coupon::where('code', $code)->first();
            if (!$coupon) {
                throw new \Exception("Mã giảm giá '$code' không tồn tại.");
            }
            if (!$coupon->is_active) {
                throw new \Exception("Mã giảm giá '$code' đã bị vô hiệu hóa.");
            }
            if ($coupon->expires_at && Carbon::parse($coupon->expires_at)->isPast()) {
                throw new \Exception("Mã giảm giá '$code' đã hết hạn.");
            }
            if ($coupon->max_uses > 0 && $coupon->uses >= $coupon->max_uses) {
                throw new \Exception("Mã giảm giá '$code' đã hết lượt sử dụng.");
            }

            if ($coupon->type === 'system') {
                $systemCount++;
            } else if ($coupon->type === 'course') {
                $courseCount++;
            }
            $coupons[] = $coupon;
        }

        if ($systemCount > 1) {
            throw new \Exception('Chỉ được áp dụng tối đa 1 mã giảm giá hệ thống.');
        }
        if ($courseCount > 1) {
            throw new \Exception('Chỉ được áp dụng tối đa 1 mã giảm giá khóa học.');
        }

        // Fetch cart contents
        $cartItems = $this->getCartContent($customerId);
        if ($cartItems->isEmpty()) {
            throw new \Exception('Giỏ hàng của bạn đang trống.');
        }

        $originalTotal = $cartItems->reduce(function ($total, $item) {
            return $total + ($item->price * $item->quantity);
        }, 0);

        $discountAmount = 0;
        $appliedToCourseId = null;
        $appliedToCourseTitle = null;
        $appliedCouponsInfo = [];

        foreach ($coupons as $coupon) {
            if ($coupon->type === 'course') {
                $matchingCartItem = $cartItems->firstWhere('course_id', $coupon->course_id);
                if (!$matchingCartItem) {
                    throw new \Exception("Mã '$coupon->code' không áp dụng cho các khóa học trong giỏ hàng.");
                }

                $coursePrice = $matchingCartItem->price;
                if ($coupon->discount_type === 'percent') {
                    $itemDiscount = $coursePrice * ($coupon->discount_value / 100);
                } else {
                    $itemDiscount = $coupon->discount_value;
                }

                if ($itemDiscount > $coursePrice) {
                    $itemDiscount = $coursePrice;
                }

                $discountAmount += $itemDiscount;
                $appliedToCourseId = $coupon->course_id;
                $appliedToCourseTitle = $matchingCartItem->course_title;
                $appliedCouponsInfo[] = [
                    'code' => $coupon->code,
                    'type' => 'course',
                    'discount_amount' => (float)$itemDiscount,
                    'course_id' => $coupon->course_id
                ];
            } else {
                if ($originalTotal < $coupon->min_order_amount) {
                    throw new \Exception("Đơn hàng chưa đạt giá trị tối thiểu " . number_format($coupon->min_order_amount) . "đ để sử dụng mã '$coupon->code'.");
                }

                if ($coupon->max_uses_per_user > 0) {
                    $userUsages = CouponUsage::where('coupon_id', $coupon->id)
                        ->where('customer_id', $customerId)
                        ->count();

                    if ($userUsages >= $coupon->max_uses_per_user) {
                        throw new \Exception("Tài khoản của bạn đã sử dụng mã '$coupon->code'.");
                    }
                }

                if ($coupon->discount_type === 'percent') {
                    $sysDiscount = $originalTotal * ($coupon->discount_value / 100);
                } else {
                    $sysDiscount = $coupon->discount_value;
                }

                $discountAmount += $sysDiscount;
                $appliedCouponsInfo[] = [
                    'code' => $coupon->code,
                    'type' => 'system',
                    'discount_amount' => (float)$sysDiscount,
                    'course_id' => null
                ];
            }
        }

        if ($discountAmount > $originalTotal) {
            $discountAmount = $originalTotal;
        }

        $finalTotal = $originalTotal - $discountAmount;
        if ($finalTotal < 0) {
            $finalTotal = 0;
        }

        return [
            'coupon_code' => $couponCode,
            'coupons_info' => $appliedCouponsInfo,
            'discount_amount' => (float)$discountAmount,
            'applied_to_course_id' => $appliedToCourseId,
            'applied_to_course_title' => $appliedToCourseTitle,
            'original_total' => (float)$originalTotal,
            'final_total' => (float)$finalTotal,
        ];
    }

    /**
     * Consume a coupon (Version with INTENTIONAL Race Condition bug).
     * Includes a delay to make race conditions extremely easy to trigger in concurrent requests.
     */
    public function consumeCouponWithBug($couponCode, $customerId, $orderId, $discountAmount)
    {
        // 1. Find coupon
        $coupon = Coupon::where('code', $couponCode)->first();
        if (!$coupon) {
            throw new \Exception('Mã giảm giá không tồn tại.');
        }

        // 2. CHECK: Is there a slot left?
        if ($coupon->max_uses > 0 && $coupon->uses >= $coupon->max_uses) {
            throw new \Exception('Mã giảm giá đã hết lượt sử dụng.');
        }

        // ⚠️ INTENTIONAL DELAY: Simulates busy backend logic / DB latency
        // This widens the race condition window so that parallel requests easily pass the check.
        usleep(250000); // 250ms

        // 3. ACT: Increment uses and save
        $coupon->uses = $coupon->uses + 1;
        $coupon->save();

        // 4. Ghi log usage
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
        ]);

        return $coupon;
    }

    /**
     * Consume a coupon using Pessimistic Locking (Fix option 1).
     */
    public function consumeCouponWithPessimisticLock($couponCode, $customerId, $orderId, $discountAmount)
    {
        return DB::transaction(function () use ($couponCode, $customerId, $orderId, $discountAmount) {
            // lockForUpdate() locks the row until the transaction commits
            $coupon = Coupon::where('code', $couponCode)
                ->lockForUpdate()
                ->first();

            if (!$coupon) {
                throw new \Exception('Mã giảm giá không tồn tại.');
            }

            if ($coupon->max_uses > 0 && $coupon->uses >= $coupon->max_uses) {
                throw new \Exception('Mã giảm giá đã hết lượt sử dụng.');
            }

            // Even with usleep here, other concurrent requests will block on lockForUpdate()
            usleep(250000);

            $coupon->increment('uses');

            CouponUsage::create([
                'coupon_id' => $coupon->id,
                'customer_id' => $customerId,
                'order_id' => $orderId,
                'discount_amount' => $discountAmount,
            ]);

            return $coupon;
        });
    }

    /**
     * Consume a coupon using Atomic Update (Fix option 2).
     */
    public function consumeCouponWithAtomicUpdate($couponCode, $customerId, $orderId, $discountAmount)
    {
        // Increment using single atomic SQL query
        $updated = Coupon::where('code', $couponCode)
            ->where(function ($q) {
                $q->where('max_uses', 0)
                    ->orWhereColumn('uses', '<', 'max_uses');
            })
            ->increment('uses');

        if ($updated === 0) {
            // Either the coupon doesn't exist, is inactive, or has run out of uses
            $coupon = Coupon::where('code', $couponCode)->first();
            if (!$coupon) {
                throw new \Exception('Mã giảm giá không tồn tại.');
            }
            throw new \Exception('Mã giảm giá đã hết lượt sử dụng.');
        }

        $coupon = Coupon::where('code', $couponCode)->first();

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
        ]);

        return $coupon;
    }

    /**
     * Get active coupons for a specific course (including course-specific and system coupons).
     */
    public function getCouponsForCourse($courseId)
    {
        $now = Carbon::now();

        return Coupon::where('is_active', 1)
            ->where(function ($q) use ($courseId) {
                $q->where(function ($sub) use ($courseId) {
                    $sub->where('type', 'course')
                        ->where('course_id', $courseId);
                })
                    ->orWhere('type', 'system');
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhere('max_uses', '=', 0)
                    ->orWhereColumn('uses', '<', 'max_uses');
            })
            ->get();
    }
}
