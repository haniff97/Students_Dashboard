<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Students extends Model
{
    use HasFactory;
    protected $table = 'students'; 
    protected $fillable = [
        'name',
        'class',
        'form',
        'subject',
        'pa1_m',
        'pa1_g',
        'ppt_m',
        'ppt_g',
        'uasa_m',
        'uasa_g',
        'tov_m',
        'tov_g',
        'etr_m',
        'etr_g',
        'year',
    ];

    
    protected $casts = [
        'pa1_m' => 'integer',
        'ppt_m' => 'integer',
        'uasa_m' => 'integer',
        'tov_m' => 'integer',
        'etr_m' => 'integer',
        'year' => 'integer',
        'form' => 'integer',
    ];

}
