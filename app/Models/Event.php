<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['producer_id', 'title', 'slug', 'description', 'venue', 'price', 'capacity', 'starts_at', 'status'])]
class Event extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'price' => 'integer',
            'capacity' => 'integer',
            'status' => EventStatus::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', EventStatus::Published);
    }

    public function isPublished(): bool
    {
        return $this->status === EventStatus::Published;
    }

    public function formattedPrice(): string
    {
        return money($this->price);
    }

    public function shareUrl(): string
    {
        return route('events.show', ['event' => $this, 'ref' => 'share']);
    }
}
