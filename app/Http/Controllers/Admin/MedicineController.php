<?php

namespace App\Http\Controllers\Admin;

use App\Models\Medicine;
use App\Services\MedicineStockTracker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MedicineController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:view_any_medicine'])->only(['index']);
        $this->middleware(['permission:create_medicine'])->only(['create', 'store']);
        $this->middleware(['permission:update_medicine'])->only(['edit', 'update', 'adjustStock']);
        $this->middleware(['permission:delete_medicine'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Medicine::query();

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        if (!is_null($request->input('is_active'))) {
            $query->where('is_active', (bool) $request->boolean('is_active'));
        }

        if ($stock = $request->input('stock_status')) {
            $query->when($stock === 'low', fn($q) => $q->lowStock()->where('stock_quantity', '>', 0))
                  ->when($stock === 'out', fn($q) => $q->where('stock_quantity', '<=', 0))
                  ->when($stock === 'available', fn($q) => $q->whereRaw('stock_quantity > minimum_stock'));
        }

        $sort = $request->input('sort', 'name');
        $dir = $request->input('dir', 'asc');

        if (!in_array($sort, ['name', 'stock_quantity', 'unit', 'created_at'])) {
            $sort = 'name';
        }
        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $medicines = $query->orderBy($sort, $dir)->paginate(15)->withQueryString();

        return view('admin.medicines.index', compact('medicines', 'sort', 'dir'));
    }

    public function create()
    {
        return view('admin.medicines.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:255',
            'unit' => 'required|string|in:tablet,kapsul,botol,tube,ampul,vial,sachet,strip',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'stock_initial' => 'nullable|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['stock_initial'] = $data['stock_initial'] ?? $data['stock_quantity'];

        Medicine::create($data);

        return redirect()->route('admin.medicines.index')->with('success', 'Obat berhasil ditambahkan');
    }

    public function edit(Medicine $medicine)
    {
        return view('admin.medicines.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:255',
            'unit' => 'required|string|in:tablet,kapsul,botol,tube,ampul,vial,sachet,strip',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'stock_initial' => 'nullable|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['stock_initial'] = $data['stock_initial'] ?? $medicine->stock_initial ?? $data['stock_quantity'];

        $medicine->update($data);

        return redirect()->route('admin.medicines.index')->with('success', 'Obat berhasil diperbarui');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();
        return redirect()->route('admin.medicines.index')->with('success', 'Obat berhasil dihapus');
    }

    public function adjustStock(Request $request, Medicine $medicine)
    {
        $data = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        $newStock = $medicine->stock_quantity + $data['adjustment'];
        if ($newStock < 0) {
            $newStock = 0;
        }
        $medicine->update(['stock_quantity' => $newStock]);

        MedicineStockTracker::adjustAdjustment($medicine, Carbon::now(), $data['adjustment']);

        return redirect()->route('admin.medicines.index')
            ->with('success', 'Stok berhasil disesuaikan');
    }
}
