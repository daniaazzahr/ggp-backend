<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagePicture extends Model
{
    use HasFactory;

    protected $table = 'pictures';

    protected $fillable = ['image'];

}
