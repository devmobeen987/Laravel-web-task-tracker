<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role']);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => [
                User::ROLE_ADMIN,
                User::ROLE_EMPLOYEE,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_EMPLOYEE])],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_EMPLOYEE])],
        ]);

        $user->update(['role' => $data['role']]);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }
}
