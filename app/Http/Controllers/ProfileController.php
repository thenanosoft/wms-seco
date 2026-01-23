<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $users = User::orderBy('id')->get();
        $maxUsers = config('wms.max_users');

        return view('profile.index', [
            'users' => $users,
            'maxUsers' => $maxUsers,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'email' => ['required','email'],
            'password' => ['nullable','min:6'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function storeUser(Request $request)
{
    $maxUsers = config('wms.max_users');

    if (User::count() >= $maxUsers) {
        throw ValidationException::withMessages([
            'users' => "Maximum {$maxUsers} users allowed.",
        ]);
    }

    $data = $request->validate([
        'name' => ['required','string','max:100'],
        'username' => ['nullable','string','max:50','unique:users,username'],
        'email' => ['required','email','unique:users,email'],
        'password' => ['required','min:6'],
    ]);

    // Auto-generate username if not provided
    $username = $data['username']
        ?? strtolower(str_replace(' ', '_', $data['name'])) . rand(100,999);

    User::create([
        'name' => $data['name'],
        'username' => $username,
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'role' => 'store_helper',
    ]);

    return back()->with('success', 'User created successfully.');
}

public function updateUser(Request $request, User $user)
{
    $data = $request->validate([
        'name' => ['required','string','max:100'],
        'username' => ['required','string','max:50','unique:users,username,' . $user->id],
        'email' => ['required','email','unique:users,email,' . $user->id],
        'password' => ['nullable','min:6'],
    ]);

    $user->name = $data['name'];
    $user->username = $data['username'];
    $user->email = $data['email'];

    if (!empty($data['password'])) {
        $user->password = Hash::make($data['password']);
    }

    $user->save();

    return back()->with('success', 'User updated successfully.');
}

public function deleteUser(User $user)
{
    if ($user->id === auth()->id()) {
        return back()->withErrors(['users' => 'You cannot delete yourself.']);
    }

    $user->delete();

    return back()->with('success', 'User deleted successfully.');
}



    public function resetUserPassword(User $user)
    {
        $newPassword = str()->random(8);

        $user->password = Hash::make($newPassword);
        $user->save();

        return back()->with('success', "Password reset. New password: {$newPassword}");
    }
}
