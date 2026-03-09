<?php

namespace App\Models\Tenant\Campaigns;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CampaignCustomer extends Pivot
{
    protected $connection = 'tenant';
    protected $table = 'cmp_campaign_customers';

    public $incrementing = true;
}
