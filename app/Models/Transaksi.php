<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis';
    protected $primaryKey = 'idTransaksi';

    protected $fillable = [
        'idUser',
        'tanggalTransaksi',
        'totalHarga',
        'status',
    ];

    /**
     * Relasi ke User (Kasir)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idUser', 'idUser');
    }

    /**
     * Relasi ke Detail Transaksi (Item)
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'idTransaksi', 'idTransaksi');
    }
}