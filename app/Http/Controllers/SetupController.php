<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SetupController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        if ($this->adminExists()) {
            return redirect('/login');
        }

        return Inertia::render('setup');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->adminExists()) {
            return redirect('/login');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_ADMIN,
        ]);

        Auth::login($admin);

        return redirect('/admin');
    }

    private function adminExists(): bool
    {
        return User::where('role', User::ROLE_ADMIN)->exists();
    }
}
