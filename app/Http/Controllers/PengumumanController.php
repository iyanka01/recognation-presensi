<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pengumuman\StorePengumumanRequest;
use App\Http\Requests\Pengumuman\UpdatePengumumanRequest;
use App\Models\Pengumuman;
use App\Services\Pengumuman\PengumumanService;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    protected PengumumanService $pengumumanService;

    public function __construct(PengumumanService $pengumumanService)
    {
        $this->pengumumanService = $pengumumanService;
    }

    public function index()
    {
        $pengumumans = Pengumuman::orderBy('created_at', 'desc')->get();
        return view('pengumuman.index', compact('pengumumans'));
    }

    public function store(StorePengumumanRequest $request)
    {
        $this->pengumumanService->store($request->validated(), $request->file('lampiran'));

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diterbitkan!');
    }

    public function update(UpdatePengumumanRequest $request, $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $this->pengumumanService->update($pengumuman, $request->validated(), $request->file('lampiran'));

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $this->pengumumanService->delete($pengumuman);

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
