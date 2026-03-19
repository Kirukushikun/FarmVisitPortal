<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permit extends Model
{
    use HasFactory;

    protected $fillable = [
        'permit_id',
        'area_id',
        'farm_location_id',
        'names',
        'date_of_visit',
        'expected_duration_hours',
        'previous_farm_location',
        'date_of_visit_previous_farm',
        'purpose',
        'remarks',
        'status',
        'created_by',
        'received_by',
        'completed_at',

        'hold_reason',
        'held_at',
        'held_by',
        'admin_response',
        'responded_at',
        'responded_by',
    ];

    protected function casts(): array
    {
        return [
            'names' => 'array',
            'area_id' => 'integer',
            'farm_location_id' => 'integer',
            'created_by' => 'integer',
            'received_by' => 'integer',
            'status' => 'integer',
            'expected_duration_hours' => 'decimal:2',
            'date_of_visit' => 'datetime',
            'date_of_visit_previous_farm' => 'datetime',
            'completed_at' => 'datetime',

            'held_at' => 'datetime',
            'responded_at' => 'datetime',
            'held_by' => 'integer',
            'responded_by' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $permit) {
            if (!$permit->permit_id) {
                $permit->permit_id = self::generatePermitId();
            }
        });
    }

    public static function generatePermitId(): string
    {
        $year = date('y');

        $lastPermit = self::query()
            ->where('permit_id', 'like', $year . '-%')
            ->orderBy('permit_id', 'desc')
            ->first();

        if ($lastPermit && is_string($lastPermit->permit_id)) {
            $lastNumber = (int) substr($lastPermit->permit_id, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $year . '-' . str_pad((string) $newNumber, 6, '0', STR_PAD_LEFT);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function farmLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'farm_location_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PermitPhoto::class);
    }

    public function heldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'held_by');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
