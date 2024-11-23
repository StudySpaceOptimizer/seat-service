<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    protected $table = 'seats';

    protected $fillable = [
        'type',
        'available',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($seat) {
            $prefix = $seat->type === 'notebook' ? 'A' : 'B';
            $count = static::where('type', $seat->type)->count() + 1;
            $seat->code = sprintf('%s%02d', $prefix, $count);
        });
    }
}
