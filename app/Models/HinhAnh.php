<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HinhAnh extends Model
{
    protected $table = 'tblhinhanh';
    public $timestamps = false;
    protected $fillable = ['CauHoiId','ten','stt','active'];
}
