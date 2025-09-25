<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoaiBangLai extends Model
{
    protected $table = 'tblloaibanglai';
    public $timestamps = false;

    // cột có sẵn trong DB: id, ten, socauhoi, mincauhoidung, active
    protected $fillable = ['ten','socauhoi','mincauhoidung','active'];

    public function cauHoiBangLai()
    {
        return $this->hasMany(CauHoiBangLai::class, 'BangLaiId', 'id');
    }
}
