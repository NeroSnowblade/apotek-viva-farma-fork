<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    use HasFactory;

    protected $table = 'obats'; // Nama tabel kustom
    protected $primaryKey = 'idObat'; // Primary key kustom

    protected $fillable = [
        'namaObat',
        'jenisObat',
        'kategori',
        'harga',
        'stok',
        'keterangan',
    ];
}