<?php

namespace App\Http\Controllers;

use App\Models\PartnerService;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DatabasePublicCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $categorySlug = mb_substr(trim((string) $request->query('category', '')), 0, 90);
        $services = PartnerService::published()->with(['category', 'partner.user'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($nested) use ($search) {
                    $nested->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                        ->orWhereHas('partner', fn ($p) => $p->whereRaw('LOWER(business_name) LIKE ?', ['%'.mb_strtolower($search).'%']));
                });
            })
            ->when($categorySlug !== '', fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
            ->orderByDesc('created_at')->paginate(12)->withQueryString();

        return view('backend-catalog.public.index', [
            'services' => $services,
            'categories' => ServiceCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'search' => $search,
            'categorySlug' => $categorySlug,
        ]);
    }

    public function show(PartnerService $partnerService): View
    {
        $service = PartnerService::published()->with(['category', 'partner.user'])
            ->findOrFail($partnerService->id);
        return view('backend-catalog.public.show', compact('service'));
    }
}
