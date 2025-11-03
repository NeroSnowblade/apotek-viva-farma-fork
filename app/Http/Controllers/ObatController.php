<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua data obat dari database
        $obats = Obat::latest()->paginate(10); // Ambil data terbaru & paginasi

        // Kirim data ke view
        return view('obats.index', compact('obats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Hanya menampilkan halaman view-nya saja
        return view('obats.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'namaObat' => 'required|string|max:100',
            'jenisObat' => 'required|string|max:50',
            'kategori' => 'nullable|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // 2. Simpan data ke database
        Obat::create([
            'namaObat' => $request->namaObat,
            'jenisObat' => $request->jenisObat,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'keterangan' => $request->keterangan,
        ]);

        // 3. Redirect ke halaman index dengan pesan sukses
        return redirect()->route('obat.index')->with('success', 'Data obat berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Obat $obat)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Obat $obat)
    {
        // Menggunakan Route Model Binding, Laravel otomatis mencari data obat berdasarkan ID
        // Kirim data obat yang ditemukan ke view 'edit'
        return view('obats.edit', compact('obat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Obat $obat)
    {
        // 1. Validasi input
        $request->validate([
            'namaObat' => 'required|string|max:100',
            'jenisObat' => 'required|string|max:50',
            'kategori' => 'nullable|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // 2. Ubah data di database
        $obat->update([
            'namaObat' => $request->namaObat,
            'jenisObat' => $request->jenisObat,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'keterangan' => $request->keterangan,
        ]);

        // 3. Redirect ke halaman index dengan pesan sukses
        return redirect()->route('obat.index')->with('success', 'Data obat berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Obat $obat)
    {
        // Hapus data obat dari database
        $obat->delete();

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('obat.index')->with('success', 'Data obat berhasil dihapus.');
    }
}
