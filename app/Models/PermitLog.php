<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitLog extends Model
{
    protected $fillable = [
        'permit_id',
        'status',
        'action',
        'changed_by',
        'message',
        'red_alert',
    ];

    protected $casts = [
        'red_alert' => 'boolean',
    ];

    // Action constants
    const ACTION_CREATED     = 0;
    const ACTION_ACCEPTED    = 1;
    const ACTION_HELD        = 2;
    const ACTION_APPROVED    = 3;
    const ACTION_REJECTED    = 4;
    const ACTION_RETURNED    = 5;
    const ACTION_RESUBMITTED = 6;
    const ACTION_COMPLETED   = 7;
    const ACTION_CANCELLED   = 8;
    const ACTION_OVERRIDE    = 9;
    const ACTION_RESOLVED_ENTERED     = 10;
    const ACTION_RESOLVED_NOT_ENTERED = 11;
    const ACTION_RESOLVED_UNVERIFIED  = 12;

    // Status constants (mirrored from Permit for convenience)
    const STATUS_SCHEDULED   = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETED   = 2;
    const STATUS_CANCELLED   = 3;
    const STATUS_ON_HOLD     = 4;
    const STATUS_RETURNED    = 5;
    const STATUS_LAPSED      = 6;
    const STATUS_RESOLVED    = 7;

    public function permit(): BelongsTo
    {
        return $this->belongsTo(Permit::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function actionLabel(): string
    {
        return match ((int) $this->action) {
            self::ACTION_CREATED              => 'Created',
            self::ACTION_ACCEPTED             => 'Accepted',
            self::ACTION_HELD                 => 'Put On Hold',
            self::ACTION_APPROVED             => 'Approved — Let Them In',
            self::ACTION_REJECTED             => 'Rejected — Turned Away',
            self::ACTION_RETURNED             => 'Returned for Correction',
            self::ACTION_RESUBMITTED          => 'Resubmitted',
            self::ACTION_COMPLETED            => 'Completed',
            self::ACTION_CANCELLED            => 'Did Not Arrive',
            self::ACTION_OVERRIDE             => 'Override — Let Them In',
            self::ACTION_RESOLVED_ENTERED     => 'Resolved — Visitor Entered',
            self::ACTION_RESOLVED_NOT_ENTERED => 'Resolved — Visitor Did Not Enter',
            self::ACTION_RESOLVED_UNVERIFIED  => 'Resolved — Outcome Unverified',
            default                           => 'Unknown',
        };
    }

    public function actionColor(): string
    {
        return match ((int) $this->action) {
            self::ACTION_CREATED              => 'gray',
            self::ACTION_ACCEPTED             => 'blue',
            self::ACTION_HELD                 => 'orange',
            self::ACTION_APPROVED             => 'green',
            self::ACTION_REJECTED             => 'red',
            self::ACTION_RETURNED             => 'purple',
            self::ACTION_RESUBMITTED          => 'blue',
            self::ACTION_COMPLETED            => 'green',
            self::ACTION_CANCELLED            => 'red',
            self::ACTION_OVERRIDE             => 'green',
            self::ACTION_RESOLVED_ENTERED     => 'teal',
            self::ACTION_RESOLVED_NOT_ENTERED => 'red',
            self::ACTION_RESOLVED_UNVERIFIED  => 'yellow',
            default                           => 'gray',
        };
    }

    public function isAdminAction(): bool
    {
        return in_array((int) $this->action, [
            self::ACTION_CREATED,
            self::ACTION_APPROVED,
            self::ACTION_REJECTED,
            self::ACTION_RETURNED,
            self::ACTION_OVERRIDE,
            self::ACTION_RESUBMITTED,
        ], true);
    }
}