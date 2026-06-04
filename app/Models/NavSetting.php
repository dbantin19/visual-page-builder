<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavSetting extends Model
{
    protected $fillable = ['alignment', 'logo_position'];

    public static function get(): self
    {
        return static::firstOrCreate([], ['alignment' => 'left']);
    }
}
