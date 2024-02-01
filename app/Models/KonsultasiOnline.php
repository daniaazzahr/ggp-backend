<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonsultasiOnline extends Model
{
    use HasFactory;

    protected $table = 'konsultasionline';

    protected $fillable = [
        'nama',
        'namaid',
        'kategori',
        'buktiTransaksi',
        'pesanKonsultasi',
        'telepon',
        'email',
        'namaPerusahaan',
        'advokat',
        'status',
        'jenisClient',
        'kota', 
        'alamat', 
        'provinsi'
    ];
}
