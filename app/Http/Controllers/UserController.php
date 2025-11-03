<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'namaUser' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users'], // Pastikan username unik
            'level' => ['required', Rule::in(['admin', 'apoteker', 'kasir'])],
            'password' => ['required', 'confirmed', Rules\Password::defaults()], // 'confirmed' akan mencocokkan dengan 'password_confirmation'
        ]);

        // 2. Simpan data ke database
        User::create([
            'namaUser' => $request->namaUser,
            'username' => $request->username,
            'level' => $request->level,
            'password' => Hash::make($request->password), // Enkripsi password
        ]);

        // 3. Redirect ke halaman index dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // 1. Validasi input
        $request->validate([
            'namaUser' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->idUser, 'idUser')], // Abaikan user saat ini saat cek unique
            'level' => ['required', Rule::in(['admin', 'apoteker', 'kasir'])],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password boleh kosong (nullable)
        ]);

        // 2. Siapkan data untuk di-update
        $updateData = [
            'namaUser' => $request->namaUser,
            'username' => $request->username,
            'level' => $request->level,
        ];

        // 3. Hanya update password jika diisi
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // 4. Update data di database
        $user->update($updateData);

        // 5. Redirect ke halaman index dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // 1. PENGAMAN: Cek apakah user mencoba menghapus diri sendiri
        if (Auth::id() == $user->idUser) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // 2. Hapus data user dari database
        $user->delete();

        // 3. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil dihapus.');
    }
}
