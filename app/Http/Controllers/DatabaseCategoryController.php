<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DatabaseCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $categories = ServiceCategory::query()->withCount('services')
            ->when($search !== '', fn ($q) => $q->where(function ($nested) use ($search) {
                $nested->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(slug) LIKE ?', ['%'.mb_strtolower($search).'%']);
            }))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('sort_order')->orderBy('name')->paginate(12)->withQueryString();

        return view('backend-catalog.admin.categories', [
            'categories' => $categories,
            'search' => $search,
            'status' => $status,
            'totals' => [
                'all' => ServiceCategory::count(),
                'active' => ServiceCategory::where('is_active', true)->count(),
                'inactive' => ServiceCategory::where('is_active', false)->count(),
            ],
        ]);
    }

    public function store(SaveServiceCategoryRequest $request): RedirectResponse
    {
        ServiceCategory::create($request->validated());
        return redirect()->route('admin.db-categories.index')->with('success', 'Kategori database berhasil ditambahkan.');
    }

    public function update(SaveServiceCategoryRequest $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        $serviceCategory->update($request->validated());
        return redirect()->route('admin.db-categories.index')->with('success', 'Kategori database diperbarui.');
    }

    public function status(Request $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $serviceCategory->update(['is_active' => (bool) $data['is_active']]);
        return back()->with('success', 'Status kategori database diperbarui.');
    }

    public function destroy(ServiceCategory $serviceCategory): RedirectResponse
    {
        DB::transaction(function () use ($serviceCategory) {
            $category = ServiceCategory::query()->lockForUpdate()->findOrFail($serviceCategory->id);
            // Soft-deleted layanan juga tetap dianggap digunakan agar relasi historis aman.
            if ($category->services()->withTrashed()->exists()) {
                throw ValidationException::withMessages([
                    'delete' => 'Kategori pernah digunakan oleh layanan. Nonaktifkan kategori, jangan hapus.',
                ]);
            }
            $category->delete();
        });
        return redirect()->route('admin.db-categories.index')->with('success', 'Kategori kosong berhasil dihapus.');
    }
}
