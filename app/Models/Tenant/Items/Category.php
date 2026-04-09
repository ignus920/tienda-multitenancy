<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Items\Items;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'inv_categories';

    protected $fillable = [
        'id',
        'name',
        'status',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'api_data_id'          // Solo el ID retornado por la API de facturación
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Verificar si la categoría está sincronizada
     */
    public function isSynced(): bool
    {
        return !empty($this->api_data_id);
    }

    /**
     * Guardar el ID de la API de facturación
     */
    public function setApiId(int $apiDataId): void
    {
        $this->update(['api_data_id' => $apiDataId]);
    }
}
