<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Obat;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;

class TransaksiController extends Controller
{
    /**
     * Menampilkan halaman riwayat transaksi.
     */
    public function index(Request $request) // <-- 1. Tambahkan Request $request
    {
        // Mulai query, tapi jangan eksekusi dulu
        $query = Transaksi::with('user')->latest();

        // 2. Terapkan filter harian jika ada
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggalTransaksi', $request->tanggal);
        }

        // 3. Terapkan filter bulanan jika ada
        if ($request->filled('bulan')) {
            // Input 'bulan' akan berformat 'YYYY-MM'
            $tanggal = \Carbon\Carbon::parse($request->bulan);
            $query->whereMonth('tanggalTransaksi', $tanggal->month)
                ->whereYear('tanggalTransaksi', $tanggal->year);
        }

        // 4. Eksekusi query dengan paginasi
        $transaksis = $query->paginate(10);

        // 5. Penting: Tambahkan query string ke link paginasi
        //    Agar saat pindah halaman, filter tetap aktif
        $transaksis->appends($request->query());

        return view('transaksi.index', compact('transaksis'));
    }

    /**
     * Menampilkan halaman untuk membuat transaksi baru.
     */
    public function create(): View
    {
        // Ambil semua obat yang stoknya lebih dari 0
        $obats = Obat::where('stok', '>', 0)->orderBy('namaObat')->get();

        // Kirim data obat ke view
        return view('transaksi.create', compact('obats'));
    }

    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi data yang masuk
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.idObat' => 'required|exists:obats,idObat',
            'items.*.jumlah' => 'required|integer|min:1',
            'totalHarga' => 'required|numeric|min:0',
        ]);

        // 2. Gunakan DB Transaction untuk memastikan integritas data
        try {
            DB::beginTransaction();

            // 3. Buat record transaksi utama
            $transaksi = Transaksi::create([
                'idUser' => Auth::id(), // ID kasir yang sedang login
                'tanggalTransaksi' => now(),
                'totalHarga' => $request->totalHarga,
            ]);

            // 4. Looping untuk menyimpan detail transaksi dan mengurangi stok
            foreach ($request->items as $item) {
                // Ambil data obat untuk mendapatkan stok terbaru
                $obat = Obat::find($item['idObat']);

                // Pastikan stok masih mencukupi
                if ($obat->stok < $item['jumlah']) {
                    // Jika stok tidak cukup, batalkan transaksi dan beri pesan error
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Stok untuk obat ' . $obat->namaObat . ' tidak mencukupi!');
                }

                // Buat record di detail_transaksis
                DetailTransaksi::create([
                    'idTransaksi' => $transaksi->idTransaksi,
                    'idObat' => $item['idObat'],
                    'jumlah' => $item['jumlah'],
                    'harga_saat_transaksi' => $item['harga_saat_transaksi'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Kurangi stok obat
                $obat->decrement('stok', $item['jumlah']);
            }

            // 5. Jika semua berhasil, commit transaksi
            DB::commit();

            // 6. Redirect dengan pesan sukses
            return redirect()->route('transaksi.invoice', $transaksi->idTransaksi)->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua query
            DB::rollBack();

            // Redirect dengan pesan error umum
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan halaman invoice/nota untuk transaksi tertentu.
     */
    public function showInvoice(Transaksi $transaksi)
    {
        // Menggunakan Route Model Binding, $transaksi sudah otomatis berisi data
        // Kita gunakan 'with' (Eager Loading) untuk mengambil relasi user dan details
        $transaksi->load(['user', 'details.obat']);

        return view('transaksi.invoice', compact('transaksi'));
    }
}
