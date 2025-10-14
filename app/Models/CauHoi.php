<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CauHoi extends Model
{
    protected $table = 'tblbocauhoi';   // đúng theo DB của bạn
    public $timestamps = false;
    // nếu PK không phải 'id' thì khai báo $primaryKey = '...';

    protected $fillable = ['stt','noidung','cauliet','active'];

    // === Quan hệ cần thêm ===
    public function dapAns()
    {
        // bảng đáp án là tblcautraloi, FK tới câu hỏi là 'CauHoiId'
        return $this->hasMany(CauTraLoi::class, 'CauHoiId', 'id');
    }

    public function hinhAnhs()
    {
        // bảng hình ảnh là tblhinhanh, FK 'CauHoiId'
        return $this->hasMany(HinhAnh::class, 'CauHoiId', 'id');
    }
}
