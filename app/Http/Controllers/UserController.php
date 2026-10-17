<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Desa;
use App\Models\User;
use App\Services\WilayahService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $desas = Desa::where('id', $currentUser->desa_id)->get();
        } elseif ($currentUser->isAdmin()) {
            if ($currentUser->isAdminPuskesmas()) {
                $cleanKec = trim(preg_replace('/^Kecamatan\s+/i', '', $currentUser->kecamatan_kerja));
                $query->where(function ($q) use ($cleanKec) {
                    $q->whereHas('desa', fn ($dq) => $dq->where('kecamatan', 'like', "%{$cleanKec}%"))
                        ->orWhere('kecamatan', 'like', "%{$cleanKec}%");
                });
                $desas = Desa::where('kecamatan', 'like', "%{$cleanKec}%")->orderBy('nama')->get();
            } else {
                $desas = Desa::orderBy('nama')->get();
            }

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
            $adminPuskesmas = null;
        } elseif ($currentUser->isAdmin()) {
            $roles = [Role::Admin, Role::Bidan, Role::Kader];
            if ($currentUser->isAdminPuskesmas()) {
                $cleanKec = trim(preg_replace('/^Kecamatan\s+/i', '', $currentUser->kecamatan_kerja));
                $desas = Desa::where('kecamatan', 'like', "%{$cleanKec}%")->orderBy('nama')->get();
                $adminPuskesmas = [
                    'kecamatan' => $currentUser->kecamatan_kerja,
                    'kabupaten' => $currentUser->kabupaten ?? $currentUser->desa?->kabupaten,
                    'provinsi' => $currentUser->provinsi ?? $currentUser->desa?->provinsi,
                ];
            } else {
                $desas = Desa::orderBy('nama')->get();
                $adminPuskesmas = null;
            }
        } else {
            abort(403);
        }

        return view('users.create', compact('roles', 'desas', 'adminPuskesmas'));
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

        // Validate user inputs FIRST so no Desa is created if inputs are incomplete or invalid
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', ValidationRule::enum(Role::class)],
            'desa_id' => [
                $request->role === Role::Admin->value ? 'nullable' : (empty($request->wilayah_desa) ? 'required' : 'nullable'),
                'exists:desas,id',
            ],
            'wilayah_desa' => ['nullable', 'string', 'max:255'],
            'wilayah_kecamatan' => ['nullable', 'string', 'max:255'],
            'wilayah_kabupaten' => ['nullable', 'string', 'max:255'],
            'wilayah_provinsi' => ['nullable', 'string', 'max:255'],
            'wilayah_kode_desa' => ['nullable', 'string', 'max:50'],
            'instansi' => ['nullable', 'string', 'max:150'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:users,nik'],
            'nip' => ['nullable', 'string', 'max:30'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'str' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
        ]);

        $roleValue = $validated['role'] instanceof Role ? $validated['role']->value : (string) $validated['role'];

        // If non-admin, ensure either existing desa_id or cascading dropdown village is provided
        if ($roleValue !== Role::Admin->value && empty($validated['desa_id']) && empty($validated['wilayah_desa'])) {
            return back()->withInput()->withErrors([
                'desa_id' => 'Wilayah desa penugasan wajib dipilih untuk Bidan atau Kader.',
            ]);
        }

        // If Admin Puskesmas adds Bidan or Kader, ensure the desa belongs to their kecamatan
        if ($currentUser->isAdminPuskesmas() && in_array($roleValue, [Role::Bidan->value, Role::Kader->value])) {
            $targetKecamatan = null;
            if (! empty($validated['desa_id'])) {
                $selectedDesa = Desa::find($validated['desa_id']);
                $targetKecamatan = $selectedDesa?->kecamatan;
            } elseif (! empty($validated['wilayah_kecamatan'])) {
                $targetKecamatan = $validated['wilayah_kecamatan'];
            }

            if (empty($targetKecamatan)) {
                return back()->withInput()->withErrors([
                    'desa_id' => 'Wilayah desa penugasan wajib dipilih untuk Bidan atau Kader.',
                ]);
            }

            $adminKec = trim(preg_replace('/^Kecamatan\s+/i', '', $currentUser->kecamatan_kerja));
            $cleanTargetKec = trim(preg_replace('/^Kecamatan\s+/i', '', $targetKecamatan));
            if (strcasecmp($cleanTargetKec, $adminKec) !== 0 && ! str_contains(strtolower($cleanTargetKec), strtolower($adminKec))) {
                return back()->withInput()->withErrors([
                    'desa_id' => "Admin Puskesmas hanya dapat menambahkan Bidan dan Kader pada wilayah kerja {$currentUser->kecamatan_kerja}.",
                ]);
            }
        }

        DB::transaction(function () use (&$validated, $currentUser) {
            // ONLY resolve or create Desa AFTER all validations have passed
            if (empty($validated['desa_id']) && ! empty($validated['wilayah_desa']) && ! empty($validated['wilayah_kecamatan'])) {
                $resolvedDesa = app(WilayahService::class)->resolveOrCreateDesa([
                    'nama' => $validated['wilayah_desa'],
                    'kecamatan' => $validated['wilayah_kecamatan'],
                    'kabupaten' => $validated['wilayah_kabupaten'] ?? '',
                    'provinsi' => $validated['wilayah_provinsi'] ?? null,
                    'kode_desa' => $validated['wilayah_kode_desa'] ?? null,
                ]);
                $validated['desa_id'] = $resolvedDesa->id;
            }

            // Clean up temporary wilayah cascading fields
            unset(
                $validated['wilayah_desa'],
                $validated['wilayah_kecamatan'],
                $validated['wilayah_kabupaten'],
                $validated['wilayah_provinsi'],
                $validated['wilayah_kode_desa']
            );

            if ($currentUser->isBidan()) {
                $validated['role'] = Role::Kader;
                $validated['desa_id'] = $currentUser->desa_id;
            }

            // If admin puskesmas registers without kecamatan, preserve puskesmas jurisdiction
            if ($currentUser->isAdminPuskesmas() && empty($validated['kecamatan'])) {
                $validated['provinsi'] = $currentUser->provinsi ?? $currentUser->desa?->provinsi;
                $validated['kabupaten'] = $currentUser->kabupaten ?? $currentUser->desa?->kabupaten;
                $validated['kecamatan'] = $currentUser->kecamatan_kerja;
            }

            $validated['password'] = Hash::make($validated['password']);
            $validated['is_active'] = true;

            User::create($validated);
        });

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
            $adminPuskesmas = null;
        } elseif ($currentUser->isAdmin()) {
            if ($currentUser->isAdminPuskesmas()) {
                $cleanKec = trim(preg_replace('/^Kecamatan\s+/i', '', $currentUser->kecamatan_kerja));
                $targetKec = trim(preg_replace('/^Kecamatan\s+/i', '', $user->kecamatan_kerja ?? ''));
                if ($user->role !== Role::Admin && $targetKec && strcasecmp($cleanKec, $targetKec) !== 0) {
                    abort(403, "Admin Puskesmas hanya dapat mengelola pengguna di wilayah {$currentUser->kecamatan_kerja}.");
                }
                $roles = [Role::Admin, Role::Bidan, Role::Kader];
                $desas = Desa::where('kecamatan', 'like', "%{$cleanKec}%")->orderBy('nama')->get();
                $adminPuskesmas = [
                    'kecamatan' => $currentUser->kecamatan_kerja,
                    'kabupaten' => $currentUser->kabupaten ?? $currentUser->desa?->kabupaten,
                    'provinsi' => $currentUser->provinsi ?? $currentUser->desa?->provinsi,
                ];
            } else {
                $roles = [Role::Admin, Role::Bidan, Role::Kader];
                $desas = Desa::orderBy('nama')->get();
                $adminPuskesmas = null;
            }
        } else {
            abort(403);
        }

        return view('users.edit', compact('user', 'roles', 'desas', 'adminPuskesmas'));
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

        // Validate user inputs FIRST
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => [$currentUser->isAdmin() ? 'required' : 'nullable', ValidationRule::enum(Role::class)],
            'desa_id' => [
                $request->role === Role::Admin->value ? 'nullable' : (empty($request->wilayah_desa) ? 'required' : 'nullable'),
                'exists:desas,id',
            ],
            'wilayah_desa' => ['nullable', 'string', 'max:255'],
            'wilayah_kecamatan' => ['nullable', 'string', 'max:255'],
            'wilayah_kabupaten' => ['nullable', 'string', 'max:255'],
            'wilayah_provinsi' => ['nullable', 'string', 'max:255'],
            'wilayah_kode_desa' => ['nullable', 'string', 'max:50'],
            'instansi' => ['nullable', 'string', 'max:150'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:users,nik,'.$user->id],
            'nip' => ['nullable', 'string', 'max:30'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'str' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $effectiveRole = $validated['role'] ?? $user->role;
        $roleValue = $effectiveRole instanceof Role ? $effectiveRole->value : (string) $effectiveRole;

        // Enforce Puskesmas district boundary if admin puskesmas
        if ($currentUser->isAdminPuskesmas() && in_array($roleValue, [Role::Bidan->value, Role::Kader->value])) {
            $targetKecamatan = null;
            if (! empty($validated['desa_id'])) {
                $selectedDesa = Desa::find($validated['desa_id']);
                $targetKecamatan = $selectedDesa?->kecamatan;
            } elseif (! empty($validated['wilayah_kecamatan'])) {
                $targetKecamatan = $validated['wilayah_kecamatan'];
            }

            if (empty($targetKecamatan)) {
                return back()->withInput()->withErrors([
                    'desa_id' => 'Wilayah desa penugasan wajib dipilih untuk Bidan atau Kader.',
                ]);
            }

            $adminKec = trim(preg_replace('/^Kecamatan\s+/i', '', $currentUser->kecamatan_kerja));
            $cleanTargetKec = trim(preg_replace('/^Kecamatan\s+/i', '', $targetKecamatan));
            if (strcasecmp($cleanTargetKec, $adminKec) !== 0 && ! str_contains(strtolower($cleanTargetKec), strtolower($adminKec))) {
                return back()->withInput()->withErrors([
                    'desa_id' => "Admin Puskesmas hanya dapat menugaskan Bidan dan Kader pada wilayah kerja {$currentUser->kecamatan_kerja}.",
                ]);
            }
        }

        DB::transaction(function () use (&$validated, $user, $currentUser) {
            // ONLY resolve or create Desa AFTER all validations have passed
            if (empty($validated['desa_id']) && ! empty($validated['wilayah_desa']) && ! empty($validated['wilayah_kecamatan'])) {
                $resolvedDesa = app(WilayahService::class)->resolveOrCreateDesa([
                    'nama' => $validated['wilayah_desa'],
                    'kecamatan' => $validated['wilayah_kecamatan'],
                    'kabupaten' => $validated['wilayah_kabupaten'] ?? '',
                    'provinsi' => $validated['wilayah_provinsi'] ?? null,
                    'kode_desa' => $validated['wilayah_kode_desa'] ?? null,
                ]);
                $validated['desa_id'] = $resolvedDesa->id;
            }

            // Clean up temporary wilayah cascading fields
            unset(
                $validated['wilayah_desa'],
                $validated['wilayah_kecamatan'],
                $validated['wilayah_kabupaten'],
                $validated['wilayah_provinsi'],
                $validated['wilayah_kode_desa']
            );

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
        });

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
