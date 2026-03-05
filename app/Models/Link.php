<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = ['complete_url', 'short_url', 'expires_at'];
    const REDIS_STOCK_KEY = 'unique_codes_stock';
    const REDIS_USED_KEY  = 'used_codes';
    
    public static function generateUniqueCode(){
        do {
            $code = \Illuminate\Support\Str::random(10);
            $isNew = \Illuminate\Support\Facades\Redis::sadd(Link::REDIS_USED_KEY, $code);
        } while (!$isNew); 
        return $code;
    }
}
