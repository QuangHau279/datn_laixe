<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoPhong extends Model
{
    protected $table = 'tblmophong';
    public $timestamps = false;

    protected $fillable = [
        'stt',
        'video',
        'diem5',
        'diem4',
        'diem3',
        'diem2',
        'diem1',
        'diem1end',
        'active'
    ];

    protected $casts = [
        'diem5' => 'integer',
        'diem4' => 'integer',
        'diem3' => 'integer',
        'diem2' => 'integer',
        'diem1' => 'integer',
        'diem1end' => 'integer',
        'active' => 'boolean',
    ];
}

