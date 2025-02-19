<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 工单.
 */
class Ticket extends Model
{
    protected $table = 'ticket';

    protected $guarded = [];

    public function scopeUid($query)
    {
        return $query->whereUserId(Auth::id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reply(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function close(): bool
    {
        $this->status = 2;

        return $this->save();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->attributes['status']) {
            0 => '<span class="badge badge-lg badge-success">'.trans('common.status.pending').'</span>',
            1 => '<span class="badge badge-lg badge-danger">'.trans('common.status.reply').'</span>',
            2 => '<span class="badge badge-lg badge-default">'.trans('common.status.closed').'</span>',
            default => '<span class="badge badge-lg badge-default">'.trans('common.status.unknown').'</span>',
        };
    }
}
