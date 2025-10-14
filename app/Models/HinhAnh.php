<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HinhAnh extends Model
{
    protected $table = 'tblhinhanh';
    public $timestamps = false;

    // cột trong bảng: id, CauHoiId, ten, stt, active
    protected $fillable = ['CauHoiId', 'ten', 'stt', 'active'];

    // tự động append trường url khi toArray()/toJson()
    protected $appends = ['url'];

    // => public/images/cauhoi/<ten>
    public function getUrlAttribute()
    {
        return asset('images/cauhoi/' . $this->ten);
    }

    public function cauHoi()
    {
        return $this->belongsTo(CauHoi::class, 'CauHoiId');
    }
}
