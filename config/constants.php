<?php
// config/constants.php
return [
  'user_status' => [
    0 => 'disabled',
    1 => 'enabled'
  ],
  'customer_status' => [
    0 => 'disable',
    1 => 'enable'
  ],
  'post_status' => [
    0 => 'private',
    1 => 'public'
  ],
  'customer_status_disable' => 0,
  'customer_status_enable' => 1,
  'per_page' => 50,
  'page' => 1,
  'SORT_ORDER' => ['DESC', 'ASC'],
  'order_status' => [
    'placed' => 1,
    'processing' => 2,
    'completed' => 3,
    'cancelled' => 4,
  ],
  'course_status_by_text' => [
    'active' => 1,
    'private' => 2
  ],
  'order_status' => [
    'placed' => 1,
    'processing' => 2,
    'completed' => 3,
    'cancelled' => 4,
  ],
  'max_room_image_upload' => 5,
  'max_capacity_image_upload' => 5000,
  'max_capacity_video_upload' => 1000000,
  'payment_transaction_status' => [
    'waiting_confirm' => 1,
    'completed' => 2,
    'failed' => 3,
  ],
  'payment_method' => [
    'vnpay' => 'vnpay'
  ],
  'vnpay_payment_url' => env('VNPAY_PAYMENT_URL'),
  'vnpay_payment_tmncode' => env('VNPAY_PAYMENT_TMNCODE'),
  'vnpay_payment_hashsecret' => env('VNPAY_PAYMENT_HASHSECRET'),
  'vnpay_payment_academy_return_url' => env('VNPAY_PAYMENT_ACADEMY_RETURN_URL'),

  'vnpay_order_type' => '190000', // 190000 Giải trí & Đào tạo
  'vnpay_lifetime' => 10, // minute
  'job_status' => [
    'inProgress' => 1,
    'success' => 2,
    'fail' => 3,
  ],
];
