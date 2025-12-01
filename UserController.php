<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua akun koordinator TA.
     */
    public function index()
    {
        $users = User::all();
        return view('user.index', compact('users'));
    }

    /**
     * Menampilkan form untuk membuat akun koordinator baru.
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * Menyimpan akun koordinator baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'                  => 'required|string|max:100',
            'username'              => 'required|string|max:50|unique:users,username',
            'email'                 => 'required|email|max:100|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'nama_koordinator' => $request->nama,
            'username'         => $request->username,
            'email'            => $request->email,
            'password'         => Hash::make($request->password),
        ]);

        return redirect()->route('poster.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    /**
     * Menghapus akun koordinator berdasarkan id_user.
     */
    public function destroy($id)
    {
        User::where('id_user', $id)->delete();
        return back()->with('success', 'Akun berhasil dihapus.');
    }

    /**
     * Menampilkan form pengaturan akun (settings).
     */
    public function editSettings()
    {
        $user = auth()->user();
        return view('user.settings', compact('user'));
    }

    /**
     * Memperbarui pengaturan akun koordinator.
     */
    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nama'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id_user . ',id_user',
            'email'    => 'required|email|max:100|unique:users,email,' . $user->id_user . ',id_user',
            'password' => 'nullable|string|min:6',
        ]);

        $user->nama_koordinator = $request->nama;
        $user->username         = $request->username;
        $user->email            = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Pengaturan akun berhasil diperbarui.');
    }
}
