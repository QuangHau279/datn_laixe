<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrafficSign extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'code',
        'title',
        'description',
        'image_path',
        'source_url',
        'source_attrib',
    ];

    public function category()
    {
        return $this->belongsTo(TrafficSignCategory::class, 'category_id');
    }
}
