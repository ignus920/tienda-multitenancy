<?php

namespace App\Services\Tenant\Campaigns;

use App\Models\Tenant\Campaigns\Campaign;
use App\Models\Tenant\Customer;
use App\Services\Tenant\Campaigns\Strategies\AssignmentStrategyInterface;
use Illuminate\Support\Collection;

class CampaignService
{
    protected Collection $strategies;

    public function __construct()
    {
        $this->strategies = collect();
        $this->registerDefaultStrategies();
    }

    protected function registerDefaultStrategies(): void
    {
        $this->registerStrategy(new Strategies\AllStrategy());
        $this->registerStrategy(new Strategies\ManualStrategy());
    }

    public function registerStrategy(AssignmentStrategyInterface $strategy): void
    {
        $this->strategies->put($strategy->getName(), $strategy);
    }

    public function checkGiftEligibility(Customer $customer, array $orderData = []): ?Campaign
    {
        $activeCampaigns = Campaign::all()->filter(fn($campaign) => $campaign->isCurrentlyActive());

        foreach ($activeCampaigns as $campaign) {
            $strategy = $this->strategies->get($campaign->assignment_type);

            if ($strategy && $strategy->shouldReceiveGift($customer, $campaign, $orderData)) {
                return $campaign;
            }
        }

        return null;
    }

    public function registerGiftSent(Campaign $campaign): void
    {
        $campaign->increment('gifts_sent');
    }
}
