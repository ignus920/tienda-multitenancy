<?php

namespace App\Models\Tenant\Transfers;

use App\Models\Tenant\Customer\VntWarehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvTransferRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tenant';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Database\Factories\Tenant\Transfers\InvTransferRequestFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inv_transfer_requests';

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
        'type',
        'date',
        'quoteId',
        'warehouseId',
        'observations',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'string',
        'quoteId' => 'integer',
        'warehouseId' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // --- Relationships ---

    /**
     * Get the warehouse associated with this transfer request.
     *
     * @return BelongsTo
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(VntWarehouse::class, 'warehouseId', 'id');
    }

    // --- Accessor Methods ---

    /**
     * Get the formatted date attribute.
     * Formats the date string for display purposes.
     *
     * @return string
     */
    public function getFormattedDateAttribute(): string
    {
        if (empty($this->date)) {
            return 'N/A';
        }

        // Try to parse the date string and format it
        try {
            $dateTime = \Carbon\Carbon::parse($this->date);
            return $dateTime->format('d/m/Y H:i');
        } catch (\Exception $e) {
            // If parsing fails, return the original date string
            return $this->date;
        }
    }

    /**
     * Get the CSS class for the status badge based on the type.
     * Returns appropriate Tailwind CSS classes for badge styling.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'REGISTRADO' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'EN PROGRESO' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'ENTREGADO' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
    }
}
