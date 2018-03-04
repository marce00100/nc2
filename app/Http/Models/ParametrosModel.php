<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ParametrosModel extends Model
{

    protected $table = 'parametros';
    public $incrementing = false;
    // cuando no se usan los campos created_at y update_at 
    public $timestamps = false;

//    protected $fillable = [
//        'fuente', 'url', 'descripcion',
//    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

}
