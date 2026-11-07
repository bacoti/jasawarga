<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(): View
    {
        $addresses = UserAddress::where('user_id', auth()->id())->orderByDesc('is_primary')->latest()->get();
        return view('account.addresses.index', compact('addresses'));
    }

    public function create(): View { return view('account.addresses.form', ['address' => new UserAddress(), 'editing' => false]); }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (UserAddress::where('user_id', $user->id)->count() >= config('jasawarga-profile.limits.addresses_per_user', 10)) {
            return back()->withInput()->withErrors(['address' => 'Maksimal 10 alamat per akun.']);
        }

        $data = $request->validated();
        $makePrimary = (bool) ($data['is_primary'] ?? false);
        unset($data['is_primary']);

        DB::transaction(function () use ($user, $data, $makePrimary) {
            $lockedUser = $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $hasAddress = UserAddress::where('user_id', $lockedUser->id)->exists();
            if ($makePrimary || ! $hasAddress) UserAddress::where('user_id', $lockedUser->id)->update(['is_primary' => false]);
            UserAddress::create($data + ['user_id' => $lockedUser->id, 'is_primary' => $makePrimary || ! $hasAddress]);
        });

        return redirect()->route('account.addresses.index')->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function edit(int $address): View
    {
        $address = UserAddress::where('user_id', auth()->id())->findOrFail($address);
        return view('account.addresses.form', ['address' => $address, 'editing' => true]);
    }

    public function update(StoreAddressRequest $request, int $address): RedirectResponse
    {
        $user = $request->user();
        $record = UserAddress::where('user_id', $user->id)->findOrFail($address);
        $data = $request->validated();
        $requestedPrimary = (bool) ($data['is_primary'] ?? false);
        unset($data['is_primary']);

        DB::transaction(function () use ($user, $record, $data, $requestedPrimary) {
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($requestedPrimary) UserAddress::where('user_id', $user->id)->where('id', '!=', $record->id)->update(['is_primary' => false]);
            $record->fill($data);
            $record->is_primary = $requestedPrimary || $record->is_primary;
            $record->save();
        });

        return redirect()->route('account.addresses.index')->with('success', 'Alamat berhasil diperbarui.');
    }

    public function makePrimary(int $address): RedirectResponse
    {
        $user = auth()->user();
        $record = UserAddress::where('user_id', $user->id)->findOrFail($address);
        DB::transaction(function () use ($user, $record) {
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            UserAddress::where('user_id', $user->id)->update(['is_primary' => false]);
            $record->update(['is_primary' => true]);
        });
        return back()->with('success', 'Alamat utama berhasil diubah.');
    }

    public function destroy(int $address): RedirectResponse
    {
        $user = auth()->user();
        $record = UserAddress::where('user_id', $user->id)->findOrFail($address);
        DB::transaction(function () use ($user, $record) {
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $wasPrimary = $record->is_primary;
            $record->delete();
            if ($wasPrimary) {
                $next = UserAddress::where('user_id', $user->id)->oldest('id')->first();
                if ($next) $next->update(['is_primary' => true]);
            }
        });
        return redirect()->route('account.addresses.index')->with('success', 'Alamat berhasil dihapus.');
    }
}
