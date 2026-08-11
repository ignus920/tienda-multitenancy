<?php

namespace App\Services\Tenant\Campaigns\Strategies;

use App\Models\Tenant\Campaigns\Campaign;
use App\Models\Tenant\Customer;

interface AssignmentStrategyInterface
{
    public function shouldReceiveGift(Customer $customer, Campaign $campaign, array $orderData = []): bool;
    public function getName(): string;
}
