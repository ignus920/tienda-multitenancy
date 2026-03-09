<?php

namespace App\Models\Tenant\Campaigns;

use App\Models\Tenant\Customer\VntCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'cmp_campaigns';

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'gift_quantity',
        'gifts_sent',
        'max_per_order',
        'assignment_type',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'gift_quantity' => 'integer',
            'gifts_sent' => 'integer',
            'max_per_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Clientes asociados manualmente a la campaña.
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(
            VntCompany::class,
            'cmp_campaign_customers',
            'campaign_id',
            'customer_id'
        )->withPivot('delivered_at')->withTimestamps();
    }

    /**
     * Determina si la campaña está actualmente activa por fecha y estado.
     */
    public function isCurrentlyActive(): bool
    {
        $today = now()->startOfDay();
        return $this->status === 'activo' 
            && $today->between($this->start_date, $this->end_date)
            && ($this->gift_quantity > $this->gifts_sent);
    }
}
