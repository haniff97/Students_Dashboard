<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Students extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class',
        'subject',
        'pat_m',
        'pat_g',
        'ppt_m',
        'ppt_g',
        'uasa_m',
        'uasa_g',
        'year'
    ];
    
    protected $casts = [
        'pat_m' => 'integer',
        'ppt_m' => 'integer',
        'uasa_m' => 'integer',
        'year' => 'integer'
    ];
}
