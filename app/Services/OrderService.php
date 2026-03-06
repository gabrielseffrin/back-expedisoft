<?php

namespace App\Services;

use App\Models\LoadingOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService
{
    public function findOrderById(string $orderId): LoadingOrder
    {
        return LoadingOrder::with('customer', 'destination', 'carrier', 'driver', 'vehicle', 'operator', 'dock', 'items.product', 'items.packages')->where('external_id', $orderId)->firstOrFail();
    }

    public function getAllOrders(string $perPage): LengthAwarePaginator
    {
        return LoadingOrder::with('customer', 'destination', 'carrier', 'driver', 'vehicle', 'operator', 'dock', 'items.product', 'items.packages')->paginate($perPage);
    }

}
