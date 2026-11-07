<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\StorePortfolioRequest;
use App\Http\Requests\Partner\UpdatePortfolioRequest;
use App\Models\PartnerPortfolio;
use App\Models\PartnerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    private function profile(): PartnerProfile
    {
        $user = auth()->user();
        return PartnerProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['business_name' => $user->name, 'business_phone' => $user->phone ?: '00000000', 'service_area' => 'Bandung', 'slug' => (Str::slug($user->name) ?: 'mitra').'-'.$user->id, 'city' => 'Bandung', 'service_areas' => []]
        );
    }

    public function index(): View
    {
        $profile = $this->profile();
        $portfolios = $profile->portfolios()->get();
        return view('partner.portfolio.index', compact('profile', 'portfolios'));
    }

    public function create(): View { return view('partner.portfolio.form', ['portfolio' => new PartnerPortfolio(), 'editing' => false]); }

    public function store(StorePortfolioRequest $request): RedirectResponse
    {
        $profile = $this->profile();
        if ($profile->portfolios()->count() >= config('jasawarga-profile.limits.portfolio_per_partner', 12)) {
            return back()->withInput()->withErrors(['portfolio' => 'Maksimal 12 item portofolio.']);
        }
        $data = $request->validated();
        $data['image_path'] = $request->file('image')->store('partners/portfolio', 'public');
        unset($data['image']);
        $data['sort_order'] = (int) $profile->portfolios()->max('sort_order') + 1;
        $profile->portfolios()->create($data);
        return redirect()->route('partner.portfolio.index')->with('success', 'Portofolio berhasil ditambahkan.');
    }

    public function edit(int $portfolio): View
    {
        $record = $this->profile()->portfolios()->findOrFail($portfolio);
        return view('partner.portfolio.form', ['portfolio' => $record, 'editing' => true]);
    }

    public function update(UpdatePortfolioRequest $request, int $portfolio): RedirectResponse
    {
        $profile = $this->profile();
        $record = $profile->portfolios()->findOrFail($portfolio);
        $data = $request->validated();
        unset($data['image']);
        if ($request->hasFile('image')) {
            $old = $record->image_path;
            $data['image_path'] = $request->file('image')->store('partners/portfolio', 'public');
        }
        $record->fill($data)->save();
        if (isset($old)) Storage::disk('public')->delete($old);
        return redirect()->route('partner.portfolio.index')->with('success', 'Portofolio berhasil diperbarui.');
    }

    public function destroy(int $portfolio): RedirectResponse
    {
        $record = $this->profile()->portfolios()->findOrFail($portfolio);
        $path = $record->image_path;
        $record->delete();
        if ($path) Storage::disk('public')->delete($path);
        return back()->with('success', 'Portofolio berhasil dihapus.');
    }
}
