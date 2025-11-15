<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoPhong extends Model
{
    protected $table = 'tblmophong';
    public $timestamps = false;

    protected $fillable = [
        'stt',
        'ma_so',
        'chuong',
        'tieu_de',
        'mo_ta_ngan',
        'video',
        'diem5',
        'diem4',
        'diem3',
        'diem2',
        'diem1',
        'diem1end',
        'diem_toi_da',
        'url_tham_khao',
        'active',
    ];

    protected $casts = [
        'stt' => 'integer',
        'chuong' => 'integer',
        'diem5' => 'decimal:3',  // DECIMAL(6,3) trong database
        'diem4' => 'decimal:3',
        'diem3' => 'decimal:3',
        'diem2' => 'decimal:3',
        'diem1' => 'decimal:3',
        'diem1end' => 'decimal:3',
        'diem_toi_da' => 'integer',
        'active' => 'boolean',
    ];
}

