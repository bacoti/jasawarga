<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PartnerProfile;
use App\Models\PartnerService;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Public discovery: only database-backed, approved and active partner accounts. */
class DiscoveryController extends Controller
{
    public function home(): View
    {
        $livePartners = $this->visiblePartners()
            ->whereHas('services', fn ($query) => $query->published())
            ->with(['services' => fn ($query) => $query->published()->with('category')->orderBy('base_price')])
            ->latest('id')->limit(4)->get();

        return view('home.index', compact('livePartners'));
    }

    public function index(Request $request): View
    {
        $dbSearch = mb_substr(trim((string) $request->query('db_q', '')), 0, 100);
        $dbCategory = mb_substr(trim((string) $request->query('db_category', '')), 0, 90);
        $dbCategories = ServiceCategory::query()->where('is_active', true)
            ->orderBy('sort_order')->orderBy('name')->get();
        // Category slug can be changed in Admin, so validate against live taxonomy.
        if ($dbCategory !== '' && ! $dbCategories->contains('slug', $dbCategory)) {
            $dbCategory = '';
        }

        $livePartners = $this->visiblePartners()
            ->with(['services' => function ($query) use ($dbCategory) {
                $query->published()->with('category')
                    ->when($dbCategory !== '', fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $dbCategory)))
                    ->orderBy('base_price');
            }])
            ->when($dbSearch !== '', function ($q) use ($dbSearch) {
                $term = '%'.mb_strtolower($dbSearch).'%';
                $q->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(business_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(service_area) LIKE ?', [$term])
                        ->orWhereHas('services', fn ($s) => $s->published()
                            ->whereRaw('LOWER(name) LIKE ?', [$term]));
                });
            })
            ->when($dbCategory !== '', fn ($q) => $q->whereHas('services', fn ($s) => $s->published()
                ->whereHas('category', fn ($c) => $c->where('slug', $dbCategory))))
            ->latest('id')->paginate(9, ['*'], 'mitra_page')->withQueryString();

        return view('categories.index', compact('livePartners', 'dbCategories', 'dbSearch', 'dbCategory'));
    }

    private function visiblePartners()
    {
        return PartnerProfile::query()
            ->where('verification_status', 'approved')
            ->where('reverification_required', false)
            ->whereNotNull('slug')
            ->whereHas('user', fn ($q) => $q->where('role', 'partner')->where('account_status', 'active')
                ->whereDoesntHave('preference', fn ($p) => $p->where('privacy->profile_visible', false)));
    }
}
