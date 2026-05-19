<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Tables\UserTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/users/index', [
            'users' => UserTable::make(User::query())->paginate(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,user'],
        ]);

        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:admin,user'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($user->isAdmin() && $data['role'] !== User::ROLE_ADMIN) {
            if (User::where('role', User::ROLE_ADMIN)->where('id', '!=', $user->id)->doesntExist()) {
                return back()
                    ->withErrors(['role' => 'Cannot demote the last admin.'])
                    ->withInput();
            }
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        if ($user->isAdmin() && User::where('role', User::ROLE_ADMIN)->where('id', '!=', $user->id)->doesntExist()) {
            return back()->with('error', 'Cannot delete the last admin.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}
