<?php

namespace App\Models\Tenant\Transfers;

use App\Models\Tenant\Items\Items;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvDetailTransferRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inv_detail_transfer_requests';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quantity',
        'quantitySend',
        'transferRequestId',
        'itemId',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'quantitySend' => 'integer',
        'transferRequestId' => 'integer',
        'itemId' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // --- Relationships ---

    /**
     * Get the transfer request that owns this detail.
     *
     * @return BelongsTo
     */
    public function transferRequest(): BelongsTo
    {
        return $this->belongsTo(InvTransferRequest::class, 'transferRequestId', 'id');
    }

    /**
     * Get the item associated with this detail.
     *
     * @return BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Items::class, 'itemId', 'id');
    }
}
