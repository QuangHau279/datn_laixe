<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CauHoiBangLai extends Model
{
    protected $table = 'tblcauhoibanglai';
    public $timestamps = false;

    // id, CauHoiId, BangLaiId, BoDe
    protected $fillable = ['CauHoiId','BangLaiId','BoDe'];
}
