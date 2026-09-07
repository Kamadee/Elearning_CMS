<?php

namespace Tests\Unit;

use App\Services\OrderServices;
use Tests\TestCase;

class OrderServicesTest extends TestCase
{
    public function test_it_filters_cms_orders_by_an_exact_order_code(): void
    {
        $query = (new OrderServices())->getOrders([
            'code' => 'OD-202608300001',
        ]);

        $this->assertStringNotContainsStringIgnoringCase(' like ', $query->toSql());
        $this->assertSame(['OD-202608300001'], $query->getBindings());
    }
}
