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


class CauHoi extends Model
{
    protected $table = 'tblcauhoi'; // nếu bạn đặt tên khác, sửa lại
    protected $fillable = ['stt', 'noi_dung', 'ten', 'active', 'is_liet', 'liet', 'isLiet', 'CauLiet'];
    public $timestamps = false;

    public function dapAn()
    {
        return $this->hasMany(CauTraLoi::class, 'CauHoiId', 'id')->orderBy('stt');
    }

    public function hinhAnh()
    {
        return $this->hasMany(HinhAnh::class, 'CauHoiId', 'id')->orderBy('stt');
    }

    // Chuẩn hóa thuộc tính "is_liet" dù cột thật có thể khác tên
    public function getIsLietNormalizedAttribute()
    {
        $attrs = $this->attributes ?? [];
        $v = $attrs['is_liet'] ?? $attrs['liet'] ?? $attrs['isLiet'] ?? $attrs['CauLiet'] ?? 0;
        return (bool)$v;
    }

    // Chuẩn hóa text câu hỏi
    public function getTextNormalizedAttribute()
    {
        return $this->noi_dung ?? $this->ten ?? null;
    }
}
