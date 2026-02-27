<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = ['complete_url', 'short_url'];

    public static function generateUniqueCode()
    {
        do {
            $code = \Illuminate\Support\Str::random(6);
        } while (self::where('short_url', $code)->exists());
        return $code;
    }
}
