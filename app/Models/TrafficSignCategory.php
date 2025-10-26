<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrafficSignCategory extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'name'];

    public function signs()
    {
        return $this->hasMany(TrafficSign::class, 'category_id');
    }
}
