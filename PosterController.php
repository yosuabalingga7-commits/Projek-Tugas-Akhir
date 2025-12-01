<?php

namespace App\Http\Controllers;

use App\Models\Poster;
use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PosterController extends Controller
{
    // Helper function untuk mendapatkan daftar topik
    private function getTopikOptions()
    {
        return [
            'Penerapan dan Pengkajian Best Practice Perangkat Lunak',
            'Machine Learning (Information Retrieval, Data Mining, Text Mining, AI, dll)',
            'Pengembangan perangkat lunak / sistem berbasis IOT',
            'Data and Information Management (cakupan ; operasional, transaksional, menengah (tactical), strategic)',
            'Business Intelligent',
            'Knowledge Management',
            'Data Warehouse',
            'Sistem Rekomendasi',
            'Image Processsing',
            'Computer Graphic',
            'Computer Vision',
            'Game and Simulator',
            'Robotics',
            'Sound Processing',
            'Math Modelling',
            'Lain-lain',
        ];
    }

    public function index(Request $request)
    {
        $query = Poster::with('anggota');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('judul_ta', 'like', '%' . $request->search . '%')
                  ->orWhere('topik_ta', 'like', '%' . $request->search . '%')
                  ->orWhere('kota', 'like', '%' . $request->search . '%')
                  ->orWhere('program_studi', 'like', '%' . $request->search . '%')
                  ->orWhere('pembimbing_1', 'like', '%' . $request->search . '%')
                  ->orWhere('pembimbing_2', 'like', '%' . $request->search . '%');
            })
            ->orWhereHas('anggota', function ($q) use ($request) {
                $q->where('nama_anggota', 'like', '%' . $request->search . '%')
                  ->orWhere('nim', 'like', '%' . $request->search . '%');
            });
        }

        $posters = $query->latest()->paginate(10)->withQueryString();

        return view('poster.index', compact('posters'));
    }

 public function semua(Request $request)
    {
        $query = Poster::with('anggota');

        // Pencarian umum (judul, topik, kota, pembimbing, dll)
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('judul_ta', 'like', "%{$keyword}%")
                  ->orWhere('topik_ta', 'like', "%{$keyword}%")
                  ->orWhere('kota', 'like', "%{$keyword}%")
                  ->orWhere('program_studi', 'like', "%{$keyword}%")
                  ->orWhere('pembimbing_1', 'like', "%{$keyword}%")
                  ->orWhere('pembimbing_2', 'like', "%{$keyword}%");
            })->orWhereHas('anggota', function ($q) use ($keyword) {
                $q->where('nama_anggota', 'like', "%{$keyword}%")
                  ->orWhere('nim', 'like', "%{$keyword}%");
            });
        }

        // FILTER salah satu saja (OR logic)
        $query->where(function ($q) use ($request) {
            if ($request->filled('tahun')) {
                $q->orWhere('tahun', intval($request->tahun));
            }
            if ($request->filled('program_studi')) {
                $q->orWhere('program_studi', $request->program_studi);
            }
            if ($request->filled('topik_ta')) {
                $q->orWhere('topik_ta', 'like', '%' . $request->topik_ta . '%');
            }
        });

        $posters = $query->latest()->paginate(8)->withQueryString();

        $allYears = Poster::select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun');
        $allProdi = Poster::select('program_studi')->distinct()->orderBy('program_studi')->pluck('program_studi');
        $allTopics = Poster::select('topik_ta')->distinct()->orderBy('topik_ta')->pluck('topik_ta');

        return view('poster.semua', compact('posters', 'allYears', 'allProdi', 'allTopics'));
    }

    public function detail($id)
    {
        $poster = Poster::with('anggota')->findOrFail($id);
        return view('poster.show', compact('poster'));
    }

    public function create()
    {
        $topics = $this->getTopikOptions();
        return view('poster.create', compact('topics'));
    }

    public function store(Request $request)
    {
        $topikOptions = $this->getTopikOptions();
        
        $validated = $request->validate([
            'judul_ta' => 'required|string|max:255|min:12',
            'topik_ta' => ['required', 'string', Rule::in($topikOptions)],
            'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'program_studi' => 'required|string',
            'kota' => 'required|string|max:100|min:3',
            'pembimbing_1' => 'required|string|max:255',
            'nip_1' => 'required|string|max:100',
            'pembimbing_2' => 'required|string|max:255',
            'nip_2' => 'required|string|max:100',
            'abstrak' => 'required|string|min:50|max:5000',
            'file_poster' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',

            'anggota_1' => 'required|string|max:255|min:4',
            'nim1' => 'required|string|unique:anggota,nim|min:9',

            'anggota_2' => 'nullable|string|max:255|min:4',
            'nim2' => 'nullable|string|different:nim1|unique:anggota,nim|min:9',

            'anggota_3' => 'nullable|string|max:255|min:4',
            'nim3' => 'nullable|string|different:nim1|different:nim2|unique:anggota,nim|min:9',
        ], [
            'judul_ta.required' => 'Judul Tugas Akhir harus diisi.',
            'judul_ta.min' => 'Judul Tugas Akhir minimal 12 karakter.',
            'topik_ta.required' => 'Topik harus dipilih.',
            'topik_ta.in' => 'Pilihan Topik tidak valid.',
            'tahun.required' => 'Tahun harus diisi.',
            'tahun.integer' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal 2000.',
            'tahun.max' => 'Tahun maksimal ' . (date('Y') + 1) . '.',
            'program_studi.required' => 'Program Studi harus dipilih.',
            'kota.required' => 'Kota harus diisi.',
            'kota.min' => 'Kota minimal 3 karakter.',
            'pembimbing_1.required' => 'Pembimbing 1 harus diisi.',
            'nip_1.required' => 'NIP Pembimbing 1 harus diisi.',
            'pembimbing_2.required' => 'Pembimbing 2 harus diisi.',
            'nip_2.required' => 'NIP Pembimbing 2 harus diisi.',
            'abstrak.required' => 'Abstrak harus diisi.',
            'abstrak.min' => 'Abstrak minimal 50 karakter.',
            'abstrak.max' => 'Abstrak maksimal 5000 karakter.',
            'file_poster.required' => 'File poster harus diunggah.',
            'file_poster.mimes' => 'File poster harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_poster.max' => 'Ukuran file poster maksimal 10MB.',
            'anggota_1.required' => 'Nama Anggota 1 harus diisi.',
            'anggota_1.min' => 'Nama Anggota 1 minimal 4 karakter.',
            'nim1.required' => 'NIM Anggota 1 harus diisi.',
            'nim1.min' => 'NIM Anggota 1 minimal 9 karakter.',
            'nim1.unique' => 'NIM Anggota 1 sudah digunakan.',
            'anggota_2.min' => 'Nama Anggota 2 minimal 4 karakter.',
            'nim2.min' => 'NIM Anggota 2 minimal 9 karakter.',
            'nim2.different' => 'NIM Anggota 2 harus berbeda dengan NIM Anggota 1.',
            'nim2.unique' => 'NIM Anggota 2 sudah digunakan.',
            'anggota_3.min' => 'Nama Anggota 3 minimal 4 karakter.',
            'nim3.min' => 'NIM Anggota 3 minimal 9 karakter.',
            'nim3.different' => 'NIM Anggota 3 harus berbeda dengan NIM Anggota 1 dan 2.',
            'nim3.unique' => 'NIM Anggota 3 sudah digunakan.',
        ]);

        $fileName = null;
        if ($request->hasFile('file_poster')) {
            $file = $request->file('file_poster');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('posters'), $fileName);
        }

        $poster = Poster::create([
            'judul_ta' => $validated['judul_ta'],
            'topik_ta' => $validated['topik_ta'],
            'tahun' => $validated['tahun'],
            'program_studi' => $validated['program_studi'],
            'kota' => $validated['kota'],
            'pembimbing_1' => $validated['pembimbing_1'],
            'pembimbing_2' => $validated['pembimbing_2'],
            'nip_1' => $validated['nip_1'],
            'nip_2' => $validated['nip_2'],
            'abstrak' => $validated['abstrak'],
            'file_poster' => $fileName,
            'id_user' => Auth::id(),
        ]);

        $anggota = [
            ['nama_anggota' => $validated['anggota_1'], 'nim' => $validated['nim1']],
            ['nama_anggota' => $validated['anggota_2'] ?? null, 'nim' => $validated['nim2'] ?? null],
            ['nama_anggota' => $validated['anggota_3'] ?? null, 'nim' => $validated['nim3'] ?? null],
        ];

        foreach ($anggota as $item) {
            if (!empty($item['nama_anggota']) && !empty($item['nim'])) {
                Anggota::create([
                    'nama_anggota' => $item['nama_anggota'],
                    'nim' => $item['nim'],
                    'id_poster' => $poster->id_poster,
                ]);
            }
        }

        return redirect()->route('poster.index')->with('success', 'Poster berhasil ditambahkan.');
    }

    public function show($id)
    {
        $poster = Poster::with('anggota')->findOrFail($id);
        return view('poster.show', compact('poster'));
    }

    public function edit($id)
    {
        $poster = Poster::with('anggota')->findOrFail($id);
        $topics = $this->getTopikOptions();
        return view('poster.edit', compact('poster', 'topics'));
    }

    public function update(Request $request, $id)
    {
        $poster = Poster::with('anggota')->findOrFail($id);
        $topikOptions = $this->getTopikOptions();

        $validated = $request->validate([
            'judul_ta' => 'required|string|max:255',
            'topik_ta' => ['required', 'string', Rule::in($topikOptions)],
            'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'program_studi' => 'required|string',
            'kota' => 'required|string|max:100',
            'pembimbing_1' => 'required|string|max:255',
            'nip_1' => 'required|string|max:100',
            'pembimbing_2' => 'required|string|max:255',
            'nip_2' => 'required|string|max:100',
            'abstrak' => 'required|string|min:50|max:5000',
            'file_poster' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'anggota_1' => 'required|string|max:255',
            'nim1' => ['required', 'string', 'min:9', Rule::unique('anggota', 'nim')->ignore($poster->anggota[0]->id_anggota ?? null, 'id_anggota')],
            'anggota_2' => 'nullable|string|max:255',
            'nim2' => ['nullable', 'string', 'different:nim1', 'min:9', Rule::unique('anggota', 'nim')->ignore($poster->anggota[1]->id_anggota ?? null, 'id_anggota')],
            'anggota_3' => 'nullable|string|max:255',
            'nim3' => ['nullable', 'string', 'different:nim1', 'different:nim2', 'min:9', Rule::unique('anggota', 'nim')->ignore($poster->anggota[2]->id_anggota ?? null, 'id_anggota')],
        ]);

        $poster->fill([
            'judul_ta' => $validated['judul_ta'],
            'topik_ta' => $validated['topik_ta'],
            'tahun' => $validated['tahun'],
            'program_studi' => $validated['program_studi'],
            'kota' => $validated['kota'],
            'pembimbing_1' => $validated['pembimbing_1'],
            'pembimbing_2' => $validated['pembimbing_2'],
            'nip_1' => $validated['nip_1'],
            'nip_2' => $validated['nip_2'],
            'abstrak' => $validated['abstrak'],
        ]);

        if ($request->hasFile('file_poster')) {
            if ($poster->file_poster && file_exists(public_path('posters/' . $poster->file_poster))) {
                unlink(public_path('posters/' . $poster->file_poster));
            }
            $file = $request->file('file_poster');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('posters'), $fileName);
            $poster->file_poster = $fileName;
        }

        $poster->save();

        $poster->anggota()->delete();

        $anggotaBaru = [
            ['nama_anggota' => $validated['anggota_1'], 'nim' => $validated['nim1']],
            ['nama_anggota' => $validated['anggota_2'] ?? null, 'nim' => $validated['nim2'] ?? null],
            ['nama_anggota' => $validated['anggota_3'] ?? null, 'nim' => $validated['nim3'] ?? null],
        ];

        foreach ($anggotaBaru as $item) {
            if (!empty($item['nama_anggota']) && !empty($item['nim'])) {
                Anggota::create([
                    'nama_anggota' => $item['nama_anggota'],
                    'nim' => $item['nim'],
                    'id_poster' => $poster->id_poster,
                ]);
            }
        }

        return redirect()->route('poster.index')->with('success', 'Poster berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $poster = Poster::findOrFail($id);

        if ($poster->file_poster && file_exists(public_path('posters/' . $poster->file_poster))) {
            unlink(public_path('posters/' . $poster->file_poster));
        }

        $poster->anggota()->delete();
        $poster->delete();

        return back()->with('success', 'Poster berhasil dihapus.');
    }

    public function downloadPdf($id)
    {
        $poster = Poster::findOrFail($id);
        $pdfPath = public_path('posters/' . $poster->file_poster);

        if (!$poster->file_poster || !file_exists($pdfPath)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return response()->download($pdfPath);
    }
}