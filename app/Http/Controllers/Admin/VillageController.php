<?php

namespace App\Http\Controllers\Admin;

use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VillageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:view_any_village'])->only(['index']);
        $this->middleware(['permission:create_village'])->only(['create', 'store']);
        $this->middleware(['permission:update_village'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_village'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = Village::query();
        if ($s = $request->input('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('district', 'like', "%{$s}%")
                    ->orWhere('regency', 'like', "%{$s}%")
                    ->orWhere('province', 'like', "%{$s}%");
            });
        }
        $villages = $q->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.villages.index', compact('villages'));
    }

    public function create()
    {
        return view('admin.villages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'sequence_number' => 'nullable|integer|min:0',
            'district' => 'nullable|string|max:255',
            'regency' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
        ]);
        Village::create($data);
        return redirect()->route('panel.villages.index')->with('success', 'Desa berhasil ditambahkan');
    }

    public function edit(Village $village)
    {
        return view('admin.villages.edit', compact('village'));
    }

    public function update(Request $request, Village $village)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'sequence_number' => 'nullable|integer|min:0',
            'district' => 'nullable|string|max:255',
            'regency' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
        ]);
        $village->update($data);
        return redirect()->route('panel.villages.index')->with('success', 'Desa berhasil diperbarui');
    }

    public function destroy(Village $village)
    {
        $village->delete();
        return redirect()->route('panel.villages.index')->with('success', 'Desa berhasil dihapus');
    }
}

