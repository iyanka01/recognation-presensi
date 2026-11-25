<?php

namespace App\Http\Controllers;

use App\Http\Requests\Departemen\StoreDepartemenRequest;
use App\Http\Requests\Departemen\UpdateDepartemenRequest;
use App\Models\Departemen;
use App\Models\Guru;
use App\Services\Departemen\DepartemenService;
use Illuminate\Http\Request;

class DepartemenController extends Controller
{
    protected DepartemenService $departemenService;

    public function __construct(DepartemenService $departemenService)
    {
        $this->departemenService = $departemenService;
    }

    public function index()
    {
        $departemens = Departemen::with('kepalaSekolah')
            ->withCount('kelas')
            ->latest()
            ->paginate(15);

        $gurus = Guru::where('status', 'Aktif')->orderBy('nama_lengkap', 'asc')->get();

        return view('departemen.index', compact('departemens', 'gurus'));
    }

    public function store(StoreDepartemenRequest $request)
    {
        $this->departemenService->store($request->validated(), $request->file('logo'));

        return redirect()->route('departemen.index')->with('success', 'Departemen baru berhasil ditambahkan!');
    }

    public function update(UpdateDepartemenRequest $request, $id)
    {
        $departemen = Departemen::findOrFail($id);
        $this->departemenService->update($departemen, $request->validated(), $request->file('logo'));

        return redirect()->route('departemen.index')->with('success', 'Data Departemen berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $departemen = Departemen::findOrFail($id);
        $result = $this->departemenService->delete($departemen);

        if (!$result['success']) {
            return redirect()->route('departemen.index')->with('error', $result['message']);
        }

        return redirect()->route('departemen.index')->with('success', $result['message']);
    }
}
