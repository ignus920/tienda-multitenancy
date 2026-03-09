<?php

namespace App\Services\Tenant\Campaigns\Strategies;

use App\Models\Tenant\Campaigns\Campaign;
use App\Models\Tenant\Customer;

class AllStrategy implements AssignmentStrategyInterface
{
    public function shouldReceiveGift(Customer $customer, Campaign $campaign, array $orderData = []): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'todos';
    }
}
