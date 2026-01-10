<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('customer.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'namaUser' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $user = User::create([
            'namaUser' => $request->namaUser,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'level' => 'customer',
        ]);

        auth()->login($user);

        return redirect()->route('customer.index')->with('success', 'Akun customer berhasil dibuat.');
    }
}
