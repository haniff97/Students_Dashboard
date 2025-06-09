<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
            'calss','year','bil', 'tingkatan','subject','bilangan_calon', 'calon_ambil', 'th', 'a_plus_bil', 'a_plus_percent',
            'a_bil', 'a_percent', 'a_minus_bil', 'a_minus_percent', 'b_plus_bil',
            'b_plus_percent', 'b_bil', 'b_percent', 'c_plus_bil', 'c_plus_percent',
            'c_bil', 'c_percent', 'd_bil', 'd_percent', 'e_bil', 'e_percent',
            'g_bil', 'g_percent', 'gp', 'lulus_percent'
        ];
}