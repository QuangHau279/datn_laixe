<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CauTraLoi extends Model
{
    protected $table = 'tblcautraloi';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['CauHoiId','stt','noidung','caudung','active'];

    public function cauHoi()
    {
        return $this->belongsTo(CauHoi::class, 'CauHoiId', 'id');
    }
}
