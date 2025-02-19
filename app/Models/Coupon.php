<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 优惠券.
 */
class Coupon extends Model
{
    use SoftDeletes;

    protected $table = 'coupon';

    protected $casts = ['limit' => 'array', 'start_time' => 'date:Y-m-d', 'end_time' => 'date:Y-m-d', 'deleted_at' => 'datetime'];

    protected $guarded = [];

    // 筛选类型
    public function scopeType($query, $type)
    {
        return $query->whereType($type);
    }

    public function setStartTimeAttribute($value)
    {
        return $this->attributes['start_time'] = strtotime($value);
    }

    public function setEndTimeAttribute($value)
    {
        return $this->attributes['end_time'] = strtotime($value);
    }

    public function used(): bool
    {
        $this->attributes['status'] = 1;

        return $this->save();
    }

    public function expired(): bool
    {
        $this->attributes['status'] = 2;

        return $this->save();
    }

    public function isExpired(): bool
    {
        return $this->attributes['end_time'] < time() || $this->attributes['status'] === 2;
    }
}
