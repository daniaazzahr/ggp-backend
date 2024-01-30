<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KlinikHukum extends Model
{
    use HasFactory;

    protected $table = 'klinikhukum';

    protected $fillable = [

        'pertanyaan',
        'jawaban',
        'kategori',
        'penulis',
        'penulisid',
        'isAnswer'
    ];
}
