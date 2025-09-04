<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoCauHoi extends Model
{
    protected $table = 'tblbocauhoi';
    public $timestamps = false;       // dump không có cột timestamps
    protected $fillable = ['stt','noidung','cauliet','giaithichdapan','active'];

    public function cauTraLoi()
    {
        // foreign key trên bảng con: CauHoiId, local key: id
        return $this->hasMany(CauTraLoi::class, 'CauHoiId', 'id')->orderBy('stt');
    }
    public function images()
    {
        // Nếu cột tblhinhanh.CauHoiId trỏ tới tblbocauhoi.ID:
        // return $this->hasMany(HinhAnh::class, 'CauHoiId', 'id');

        // Nếu CauHoiId trỏ tới STT (nhìn ảnh có vẻ bạn lưu theo số câu):
        return $this->hasMany(HinhAnh::class, 'CauHoiId', 'id');
    }
}