<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant\Movements\InvStore;

class VntContact extends Model
{
    use HasFactory;

    protected $connection = 'central';
    protected $table = 'vnt_contacts';

    protected $fillable = [
        'firstName',
        'secondName',
        'lastName',
        'secondLastName',
        'email',
        'phone_contact',
        'contact',
        'status',
        'integrationDataId',
        'warehouseId',
        'positionId',
        'store'
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(VntWarehouse::class, 'warehouseId');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(CnfPosition::class, 'positionId');
    }

    /**
     * Get the selected store from the tenant database.
     * Returns null if no store is selected.
     *
     * @return InvStore|null
     */
    public function selectedStore(): ?InvStore
    {
        if (!$this->store) {
            return null;
        }
        return InvStore::on('tenant')->find($this->store);
    }
}