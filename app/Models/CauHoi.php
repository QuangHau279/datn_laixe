<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CauTraLoi;

class CauHoi extends Model
{
    protected $table = 'tblbocauhoi';
    public $timestamps = false;

    // id, stt, noidung, cauliet, giaithichdapan, active
    protected $fillable = ['stt','noidung','cauliet','giaithichdapan','active'];

    public function dapAn()
    {
        return $this->hasMany(CauTraLoi::class, 'CauHoiId', 'id')->orderBy('stt');
    }
}
