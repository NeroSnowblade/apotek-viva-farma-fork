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
        'idObat',
        'jumlah',
        'harga_saat_transaksi',
        'subtotal',
    ];

    /**
     * Relasi ke Obat
     */
    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'idObat', 'idObat');
    }
}
