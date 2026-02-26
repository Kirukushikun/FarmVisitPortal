<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permit extends Model
{
    use HasFactory;

    protected $fillable = [
        'permit_id',
        'area',
        'farm_location_id',
        'names',
        'area_to_visit',
        'destination_location_id',
        'date_of_visit',
        'expected_duration_seconds',
        'previous_farm_location_id',
        'date_of_visit_previous_farm',
        'purpose',
        'status',
        'created_by',
        'received_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'farm_location_id' => 'integer',
            'destination_location_id' => 'integer',
            'previous_farm_location_id' => 'integer',
            'created_by' => 'integer',
            'received_by' => 'integer',
            'status' => 'integer',
            'expected_duration_seconds' => 'integer',
            'date_of_visit' => 'datetime',
            'date_of_visit_previous_farm' => 'datetime',
            'completed_at' => 'datetime',
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

    public function farmLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'farm_location_id');
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function previousFarmLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'previous_farm_location_id');
    }
}
