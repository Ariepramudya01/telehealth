<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Desa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        $query = User::with('desa');

        if ($currentUser->isBidan()) {
            // Bidan can only view kaders in her village
            $query->where('desa_id', $currentUser->desa_id)
                ->where('role', Role::Kader);
        } elseif ($currentUser->isAdmin()) {
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }
            if ($request->filled('desa_id')) {
                $query->where('desa_id', $request->desa_id);
            }
        } else {
            abort(403);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $desas = Desa::orderBy('nama')->get();

        return view('users.index', compact('users', 'desas'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $currentUser = Auth::user();

        if ($currentUser->isBidan()) {
            // Bidan can only add Kader
            $roles = [Role::Kader];
            $desas = Desa::where('id', $currentUser->desa_id)->get();
        } elseif ($currentUser->isAdmin()) {
            $roles = [Role::Admin, Role::Bidan, Role::Kader];
            $desas = Desa::orderBy('nama')->get();
        } else {
            abort(403);
        }

        return view('users.create', compact('roles', 'desas'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();

        // Bidan can only add Kader
        if ($currentUser->isBidan() && $request->role !== Role::Kader->value) {
            abort(403, 'Bidan hanya diperbolehkan menambahkan kader, bukan bidan atau admin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', ValidationRule::enum(Role::class)],
            'desa_id' => [
                $request->role === Role::Admin->value ? 'nullable' : 'required',
                'exists:desas,id',
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:users,nik'],
            'alamat' => ['nullable', 'string'],
        ]);

        if ($currentUser->isBidan()) {
            $validated['role'] = Role::Kader;
            $validated['desa_id'] = $currentUser->desa_id;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna baru berhasil didaftarkan.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $currentUser = Auth::user();

        if ($currentUser->isBidan()) {
            if ($user->role !== Role::Kader || $user->desa_id !== $currentUser->desa_id) {
                abort(403, 'Bidan hanya dapat mengelola data kader di desanya.');
            }
            $roles = [Role::Kader];
            $desas = Desa::where('id', $currentUser->desa_id)->get();
        } elseif ($currentUser->isAdmin()) {
            $roles = [Role::Admin, Role::Bidan, Role::Kader];
            $desas = Desa::orderBy('nama')->get();
        } else {
            abort(403);
        }

        return view('users.edit', compact('user', 'roles', 'desas'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        if ($currentUser->isBidan()) {
            if ($user->role !== Role::Kader || $user->desa_id !== $currentUser->desa_id) {
                abort(403, 'Bidan hanya dapat memperbarui data kader di desanya.');
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => [$currentUser->isAdmin() ? 'required' : 'nullable', ValidationRule::enum(Role::class)],
            'desa_id' => [
                $request->role === Role::Admin->value ? 'nullable' : 'required',
                'exists:desas,id',
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:users,nik,'.$user->id],
            'alamat' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($currentUser->isBidan()) {
            $validated['role'] = Role::Kader;
            $validated['desa_id'] = $currentUser->desa_id;
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        if ($currentUser->id === $user->id) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        if ($currentUser->isBidan()) {
            if ($user->role !== Role::Kader || $user->desa_id !== $currentUser->desa_id) {
                abort(403, 'Bidan hanya dapat menghapus kader di desanya.');
            }
        } elseif (! $currentUser->isAdmin()) {
            abort(403);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}

