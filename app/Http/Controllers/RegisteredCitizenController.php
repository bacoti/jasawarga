<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisteredCitizenController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));
        abort_if(mb_strlen($term) > 100, 422, 'Kata pencarian terlalu panjang.');
        $users = User::query()->where('role', User::ROLE_CITIZEN)
            ->when($term !== '', fn ($q) => $q->where(function ($nested) use ($term) {
                $nested->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($term).'%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($term).'%']);
            }))
            ->latest()->paginate(20)->withQueryString();
        return view('auth.admin-citizen-accounts', compact('users', 'term'));
    }
}
