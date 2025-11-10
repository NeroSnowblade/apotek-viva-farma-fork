<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailTransaksi extends Model
{
    use HasFactory;
    
    protected $table = 'detail_transaksis';
    protected $primaryKey = 'idDetailTransaksi';

    protected $fillable = [
        'idTransaksi',
        'nama_obat',
        'jumlah',
        'harga_saat_transaksi',
        'subtotal',
    ];

    /**
     * Relasi ke Obat
     */
    // No longer maintain a relation to Obat; we store nama_obat as plain text
}
