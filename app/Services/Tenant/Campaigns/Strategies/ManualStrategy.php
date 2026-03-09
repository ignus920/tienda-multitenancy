<?php

namespace App\Services\Tenant\Campaigns\Strategies;

use App\Models\Tenant\Campaigns\Campaign;
use App\Models\Tenant\Customer;

class ManualStrategy implements AssignmentStrategyInterface
{
    public function shouldReceiveGift(Customer $customer, Campaign $campaign, array $orderData = []): bool
    {
        return $campaign->customers()->where('customer_id', $customer->id)->exists();
    }

    public function getName(): string
    {
        return 'manual';
    }
}
