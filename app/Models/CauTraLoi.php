<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CauTraLoi extends Model
{
    protected $table = 'tblcautraloi';
    public $timestamps = false;
    protected $fillable = ['CauHoiId','stt','noidung','caudung','active'];
}
