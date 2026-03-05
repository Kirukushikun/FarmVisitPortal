<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'name',
        'is_disabled',
    ];

    protected function casts(): array
    {
        return [
            'location_id' => 'integer',
            'is_disabled' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
