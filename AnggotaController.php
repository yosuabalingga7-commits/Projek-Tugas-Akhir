<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;

class AnggotaController extends Controller
{
    /**
     * Menampilkan semua data anggota.
     */
    public function index()
    {
        $anggota = Anggota::all();
        return view('anggota.index', compact('anggota'));
    }

    /**
     * Menampilkan form tambah anggota.
     */
    public function create()
    {
        return view('anggota.create');
    }

    /**
     * Menyimpan data anggota baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_anggota' => 'required|string|max:255',
            'nim' => 'required|numeric|unique:anggota,nim',
        ]);

        Anggota::create([
            'nama_anggota' => $request->nama_anggota,
            'nim' => $request->nim,
        ]);

        return redirect()->route('anggota.index')->with('success', 'Data anggota berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit anggota.
     */
    public function edit($id)
    {
        $anggota = Anggota::findOrFail($id);
        return view('anggota.edit', compact('anggota'));
    }

    /**
     * Memperbarui data anggota.
     */
    public function update(Request $request, $id)
    {
        $anggota = Anggota::findOrFail($id);

        $request->validate([
            'nama_anggota' => 'required|string|max:255',
            'nim' => 'required|numeric|unique:anggota,nim,' . $anggota->id,
        ]);

        $anggota->update([
            'nama_anggota' => $request->nama_anggota,
            'nim' => $request->nim,
        ]);

        return redirect()->route('anggota.index')->with('success', 'Data anggota berhasil diperbarui.');
    }

    /**
     * Menghapus data anggota.
     */
    public function destroy($id)
    {
        $anggota = Anggota::findOrFail($id);
        $anggota->delete();

        return back()->with('success', 'Data anggota berhasil dihapus.');
    }
}
    