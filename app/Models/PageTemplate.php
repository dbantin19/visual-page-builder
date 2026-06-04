<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageTemplate extends Model
{
    protected $fillable = ['name', 'content', 'builder_data'];
}
