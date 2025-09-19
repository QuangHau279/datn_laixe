<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoaiBangLai extends Model
{
    protected $table = 'tblloaiBanglai';
    public $timestamps = false;
    protected $fillable = ['ma', 'ten', 'active'];
}
