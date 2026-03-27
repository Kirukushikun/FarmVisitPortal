<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permit extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_SCHEDULED   = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETED   = 2;
    const STATUS_CANCELLED   = 3;
    const STATUS_ON_HOLD     = 4;
    const STATUS_RETURNED    = 5;

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
        'red_alert',
        'status',
        'created_by',
        'received_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'names'                       => 'array',
            'area_id'                     => 'integer',
            'farm_location_id'            => 'integer',
            'created_by'                  => 'integer',
            'received_by'                 => 'integer',
            'status'                      => 'integer',
            'expected_duration_hours'     => 'decimal:2',
            'date_of_visit'               => 'datetime',
            'date_of_visit_previous_farm' => 'datetime',
            'completed_at'                => 'datetime',
            'red_alert'                   => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $permit) {
            if (! $permit->permit_id) {
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
            $newNumber  = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $year . '-' . str_pad((string) $newNumber, 6, '0', STR_PAD_LEFT);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

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

    public function logs(): HasMany
    {
        return $this->hasMany(PermitLog::class)->orderBy('created_at', 'asc');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function lastAdminLog(): ?PermitLog
    {
        return $this->logs()
            ->whereIn('action', [
                PermitLog::ACTION_APPROVED,
                PermitLog::ACTION_REJECTED,
                PermitLog::ACTION_RETURNED,
                PermitLog::ACTION_OVERRIDE,
            ])
            ->latest()
            ->first();
    }

    public function wasAdminApproved(): bool
    {
        $log = $this->lastAdminLog();
        if (! $log) return false;
        return in_array((int) $log->action, [
            PermitLog::ACTION_APPROVED,
            PermitLog::ACTION_OVERRIDE,
        ], true);
    }

    public function wasAdminRejected(): bool
    {
        $log = $this->lastAdminLog();
        if (! $log) return false;
        return (int) $log->action === PermitLog::ACTION_REJECTED;
    }

    public function redAlertGroups(): array
    {
        $names = $this->names;
        if (! is_array($names) || ($names['mode'] ?? '') !== 'detailed') {
            return [];
        }

        $farmType     = (int) ($this->farmLocation?->farm_type ?? 0);
        $requiredDays = $farmType === 1 ? 3 : 5;
        $visitDate    = \Carbon\Carbon::parse($this->date_of_visit)->startOfDay();
        $alertGroups  = [];

        foreach ($names['groups'] ?? [] as $i => $group) {
            $dateVisited = $group['date_visited'] ?? '';
            if ($dateVisited === '') continue;
            $prev = \Carbon\Carbon::parse($dateVisited)->startOfDay();
            if ($prev->diffInDays($visitDate) < $requiredDays) {
                $alertGroups[] = $i;
            }
        }

        return $alertGroups;
    }
}