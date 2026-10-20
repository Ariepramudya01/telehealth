<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Desa;
use App\Models\User;
use App\Services\WhatsAppService;
use App\Services\WilayahService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        $query = User::with('desa')->where('id', '!=', $currentUser->id);
        $kecamatanList = collect();

        if ($currentUser->isBidan()) {
            // Bidan can view and manage kaders in her village / puskesmas jurisdiction
            $desaIds = Desa::where('kecamatan', $currentUser->desa?->kecamatan)
                ->orWhere('puskesmas', $currentUser->desa?->puskesmas)
                ->orWhere('id', $currentUser->desa_id)
                ->pluck('id')
                ->all();
            if (empty($desaIds)) {
                $desaIds = [$currentUser->desa_id];
            }

            $query->whereIn('desa_id', $desaIds)
                ->where('role', Role::Kader);
            $desas = Desa::whereIn('id', $desaIds)->orderBy('nama')->get();
        } elseif ($currentUser->isAdmin()) {
            if ($currentUser->isAdminPuskesmas()) {
                $allDesas = Desa::with('users')->get();
                $desas = $allDesas->filter(fn ($d) => $currentUser->canAccessDesa($d))->values();
                $accessibleDesaIds = $desas->pluck('id')->toArray();
                $cleanPusk = $currentUser->clean_puskesmas_keyword;

                $query->where(function ($q) use ($accessibleDesaIds, $cleanPusk) {
                    $q->whereIn('desa_id', $accessibleDesaIds);
                    if ($cleanPusk) {
                        $q->orWhere('instansi', 'like', "%{$cleanPusk}%");
                    }
                });

                $kecamatanList = $desas->pluck('kecamatan')
                    ->filter()
                    ->map(fn ($k) => strtoupper(trim(preg_replace('/^(kecamatan|kec\.?)\s+/i', '', $k))))
                    ->unique()
                    ->sort()
                    ->values();
            } elseif ($currentUser->isDinkesKabupaten()) {
                // Khusus Dinkes Kabupaten: hanya munculkan data user yang berada di kabupaten sesuai wilayah tersebut
                $userKab = $currentUser->kabupaten;
                $cleanKab = strtolower(trim(preg_replace('/^(kabupaten|kota)\s+/i', '', (string) $userKab)));

                $query->where(function ($q) use ($cleanKab) {
                    if ($cleanKab) {
                        $q->whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"])
                            ->orWhereHas('desa', fn ($sub) => $sub->whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"]))
                            ->orWhereRaw('LOWER(instansi) LIKE ?', ["%{$cleanKab}%"]);
                    }
                });

                // Desa di kabupaten tersebut
                $desas = Desa::whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"])->orderBy('nama')->get();

                // Daftar kecamatan di kabupaten tersebut
                $dbKecs = Desa::whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"])
                    ->whereNotNull('kecamatan')
                    ->distinct()
                    ->pluck('kecamatan')
                    ->merge(
                        User::whereRaw('LOWER(kabupaten) LIKE ?', ["%{$cleanKab}%"])
                            ->whereNotNull('kecamatan')
                            ->distinct()
                            ->pluck('kecamatan')
                    );

                foreach ($dbKecs as $k) {
                    $c = trim(preg_replace('/^(kecamatan|kec\.?)\s+/i', '', $k));
                    if ($c) {
                        $kecamatanList->push(strtoupper($c));
                    }
                }

                if (str_contains($cleanKab, 'indragiri hulu')) {
                    $inhuKecs = ['BATANG CENAKU', 'BATANG GANSAL', 'BATANG PERANAP', 'KELAYANG', 'KUALA CENAKU', 'LIRIK', 'LUBUK BATU JAYA', 'PASIR PENYU', 'PERANAP', 'RAKIT KULIM', 'RENGAT', 'RENGAT BARAT', 'SEBERIDA', 'SUNGAI LALA'];
                    $kecamatanList = $kecamatanList->merge($inhuKecs);
                }

                $kecamatanList = $kecamatanList->filter()->unique()->sort()->values();
            } else {
                // Dinkes Provinsi (Admin Utama)
                $desas = Desa::orderBy('nama')->get();
                $kecamatanList = Desa::whereNotNull('kecamatan')->distinct()->pluck('kecamatan')
                    ->merge(User::whereNotNull('kecamatan')->distinct()->pluck('kecamatan'))
                    ->map(fn ($k) => strtoupper(trim(preg_replace('/^(kecamatan|kec\.?)\s+/i', '', $k))))
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();
            }

            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }
            if ($request->filled('kecamatan')) {
                $filterKec = trim($request->kecamatan);
                $cleanFilterKec = strtolower(trim(preg_replace('/^(kecamatan|kec\.?)\s+/i', '', $filterKec)));
                $query->where(function ($q) use ($cleanFilterKec) {
                    $q->whereRaw('LOWER(kecamatan) LIKE ?', ["%{$cleanFilterKec}%"])
                        ->orWhereHas('desa', fn ($sub) => $sub->whereRaw('LOWER(kecamatan) LIKE ?', ["%{$cleanFilterKec}%"]));
                });
            }
            if ($request->filled('desa_id')) {
                $query->where('desa_id', $request->desa_id);
            }
        } else {
            abort(403);
        }

        $pendingCount = (clone $query)->where('is_active', false)->count();
        $users = $query->latest()->paginate(15)->withQueryString();

        return view('users.index', compact('users', 'desas', 'pendingCount', 'kecamatanList'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $currentUser = Auth::user();

        if ($currentUser->isBidan()) {
            // Bidan can only add Kader within her puskesmas/kecamatan villages
            $roles = [Role::Kader];
            $desas = Desa::where('kecamatan', $currentUser->desa?->kecamatan)
                ->orWhere('puskesmas', $currentUser->desa?->puskesmas)
                ->orWhere('id', $currentUser->desa_id)
                ->orderBy('nama')
                ->get();
            if ($desas->isEmpty()) {
                $desas = Desa::where('id', $currentUser->desa_id)->get();
            }
            $adminPuskesmas = null;
        } elseif ($currentUser->isAdmin()) {
            if ($currentUser->isAdminPuskesmas()) {
                $roles = [Role::Bidan, Role::Kader];
                $allDesas = Desa::with('users')->get();
                $desas = $allDesas->filter(fn ($d) => $currentUser->canAccessDesa($d))->values();
                $adminPuskesmas = [
                    'kecamatan' => $currentUser->kecamatan_kerja ?? $desas->first()?->kecamatan,
                    'kabupaten' => $currentUser->kabupaten ?? $currentUser->desa?->kabupaten ?? $desas->first()?->kabupaten,
                    'provinsi' => $currentUser->provinsi ?? $currentUser->desa?->provinsi ?? $desas->first()?->provinsi,
                ];
            } else {
                $roles = [Role::Admin, Role::Bidan, Role::Kader];
                $desas = Desa::orderBy('nama')->get();
                $adminPuskesmas = null;
            }
        } else {
            abort(403);
        }

        $apiVillages = [];
        if ($currentUser->isBidan() || ($currentUser->isAdmin() && ! $currentUser->isDinasOrPusat())) {
            $apiVillages = app(WilayahService::class)->getApiVillagesForUser($currentUser);
        }

        return view('users.create', compact('roles', 'desas', 'adminPuskesmas', 'apiVillages'));
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

        // Admin Puskesmas can only add Bidan or Kader, NOT Admin
        if ($currentUser->isAdminPuskesmas() && $request->role === Role::Admin->value) {
            abort(403, 'Admin Puskesmas (Kepala Puskesmas / Kepala TU) tidak berwenang menambahkan akun Admin.');
        }

        // Only Dinkes Provinsi (Admin Utama) can create provincial / central admin accounts
        if ($request->role === Role::Admin->value && ! $currentUser->isDinkesProvinsi()) {
            $instansiReq = strtolower((string) $request->input('instansi'));
            $jabatanReq = strtolower((string) $request->input('jabatan'));
            if (str_contains($instansiReq, 'provinsi') || str_contains($instansiReq, 'prov') || str_contains($jabatanReq, 'utama') || str_contains($jabatanReq, 'provinsi')) {
                abort(403, 'Hanya Dinkes Provinsi (Admin Utama) yang berwenang membuat akun tingkat Provinsi / Admin Utama.');
            }
        }

        // Validate user inputs FIRST so no Desa is created if inputs are incomplete or invalid
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
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

        // If Admin Puskesmas adds Bidan or Kader, ensure the desa belongs to their territory
        if ($currentUser->isAdminPuskesmas() && in_array($roleValue, [Role::Bidan->value, Role::Kader->value])) {
            if (! empty($validated['desa_id'])) {
                $selectedDesa = Desa::with('users')->find($validated['desa_id']);
                if (! $selectedDesa || ! $currentUser->canAccessDesa($selectedDesa)) {
                    return back()->withInput()->withErrors([
                        'desa_id' => 'Admin Puskesmas hanya dapat menambahkan Bidan dan Kader pada desa di wilayah kerja Puskesmas Anda.',
                    ]);
                }
            } elseif (empty($validated['wilayah_desa'])) {
                return back()->withInput()->withErrors([
                    'desa_id' => 'Wilayah desa penugasan wajib dipilih untuk Bidan atau Kader.',
                ]);
            }
        }

        $createdUser = null;

        DB::transaction(function () use (&$validated, $currentUser, $request, &$createdUser) {
            // ONLY resolve or create Desa AFTER all validations have passed
            if (empty($validated['desa_id']) && ! empty($validated['wilayah_desa'])) {
                $resolvedDesa = app(WilayahService::class)->resolveOrCreateDesa([
                    'nama' => $validated['wilayah_desa'],
                    'kecamatan' => $validated['wilayah_kecamatan'] ?? ($currentUser->desa?->kecamatan ?? 'Kecamatan Terkait'),
                    'kabupaten' => $validated['wilayah_kabupaten'] ?? ($currentUser->desa?->kabupaten ?? 'Kabupaten Terkait'),
                    'provinsi' => $validated['wilayah_provinsi'] ?? ($currentUser->desa?->provinsi ?? null),
                    'kode_desa' => $validated['wilayah_kode_desa'] ?? null,
                ]);
                if (empty($resolvedDesa->puskesmas) && $currentUser->instansi) {
                    $resolvedDesa->update(['puskesmas' => $currentUser->instansi]);
                }
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
                $allowedDesaIds = Desa::where('kecamatan', $currentUser->desa?->kecamatan)
                    ->orWhere('puskesmas', $currentUser->desa?->puskesmas)
                    ->orWhere('id', $currentUser->desa_id)
                    ->pluck('id')
                    ->all();
                if ($request->filled('desa_id') && in_array((int) $request->input('desa_id'), $allowedDesaIds)) {
                    $validated['desa_id'] = (int) $request->input('desa_id');
                } else {
                    $validated['desa_id'] = $currentUser->desa_id;
                }
            }

            // If admin puskesmas registers without kecamatan, preserve puskesmas jurisdiction
            if ($currentUser->isAdminPuskesmas() && empty($validated['kecamatan'])) {
                $validated['provinsi'] = $currentUser->provinsi ?? $currentUser->desa?->provinsi;
                $validated['kabupaten'] = $currentUser->kabupaten ?? $currentUser->desa?->kabupaten;
                $validated['kecamatan'] = $currentUser->kecamatan_kerja;
            }

            $hasCustomPassword = ! empty($validated['password']);
            $rawPassword = $hasCustomPassword ? $validated['password'] : Str::random(24);
            $validated['password'] = Hash::make($rawPassword);
            $validated['is_active'] = $hasCustomPassword;
            $validated['activation_token'] = Str::random(60);
            $validated['activation_expires_at'] = now()->addDays(7);

            $createdUser = User::create($validated);
        });

        if ($createdUser && ! empty($createdUser->phone)) {
            try {
                app(WhatsAppService::class)->sendActivationLink($createdUser);
            } catch (\Throwable $e) {
                // Gateway may be offline or unconfigured
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'Pengguna baru berhasil didaftarkan.')
            ->with('new_user_id', $createdUser?->id)
            ->with('new_user_name', $createdUser?->name)
            ->with('new_user_phone', $createdUser?->phone)
            ->with('new_user_wa_url', $createdUser?->phone ? $createdUser->whatsapp_activation_url : null)
            ->with('new_user_activation_url', $createdUser?->activation_url);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $currentUser = Auth::user();

        if (! $currentUser->canEditUser($user)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit akun ini.');
        }

        if ($currentUser->isBidan()) {
            $desaIds = Desa::where('kecamatan', $currentUser->desa?->kecamatan)
                ->orWhere('puskesmas', $currentUser->desa?->puskesmas)
                ->orWhere('id', $currentUser->desa_id)
                ->pluck('id')
                ->all();
            if (empty($desaIds)) {
                $desaIds = [$currentUser->desa_id];
            }

            if ($user->role !== Role::Kader || ! in_array($user->desa_id, $desaIds)) {
                abort(403, 'Bidan hanya dapat mengelola data kader di wilayah kerjanya.');
            }
            $roles = [Role::Kader];
            $desas = Desa::whereIn('id', $desaIds)->orderBy('nama')->get();
            $adminPuskesmas = null;
        } elseif ($currentUser->isAdmin()) {
            if ($currentUser->isAdminPuskesmas()) {
                if ($user->role !== Role::Admin && ! $currentUser->canAccessStaff($user)) {
                    abort(403, 'Admin Puskesmas hanya dapat mengelola pengguna di wilayah kerja Puskesmas Anda.');
                }
                $roles = $user->role === Role::Admin ? [Role::Admin] : [Role::Bidan, Role::Kader];
                $allDesas = Desa::with('users')->get();
                $desas = $allDesas->filter(fn ($d) => $currentUser->canAccessDesa($d))->values();
                $adminPuskesmas = [
                    'kecamatan' => $currentUser->kecamatan_kerja ?? $desas->first()?->kecamatan,
                    'kabupaten' => $currentUser->kabupaten ?? $currentUser->desa?->kabupaten ?? $desas->first()?->kabupaten,
                    'provinsi' => $currentUser->provinsi ?? $currentUser->desa?->provinsi ?? $desas->first()?->provinsi,
                ];
            } else {
                $roles = [Role::Admin, Role::Bidan, Role::Kader];
                $desas = Desa::orderBy('nama')->get();
                $adminPuskesmas = null;
            }
        } else {
            abort(403);
        }

        $apiVillages = [];
        if ($currentUser->isBidan() || ($currentUser->isAdmin() && ! $currentUser->isDinasOrPusat())) {
            $apiVillages = app(WilayahService::class)->getApiVillagesForUser($currentUser);
        }

        return view('users.edit', compact('user', 'roles', 'desas', 'adminPuskesmas', 'apiVillages'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        if (! $currentUser->canEditUser($user)) {
            abort(403, 'Anda tidak memiliki hak akses untuk memperbarui akun ini.');
        }

        if ($currentUser->isBidan()) {
            $desaIds = Desa::where('kecamatan', $currentUser->desa?->kecamatan)
                ->orWhere('puskesmas', $currentUser->desa?->puskesmas)
                ->orWhere('id', $currentUser->desa_id)
                ->pluck('id')
                ->all();
            if (empty($desaIds)) {
                $desaIds = [$currentUser->desa_id];
            }

            if ($user->role !== Role::Kader || ! in_array($user->desa_id, $desaIds)) {
                abort(403, 'Bidan hanya dapat memperbarui data kader di wilayah kerjanya.');
            }
        }

        if ($currentUser->isAdminPuskesmas() && $request->role === Role::Admin->value && $user->role !== Role::Admin) {
            abort(403, 'Admin Puskesmas tidak berwenang mengubah peran pengguna menjadi Admin.');
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
            if (! empty($validated['desa_id'])) {
                $selectedDesa = Desa::with('users')->find($validated['desa_id']);
                if (! $selectedDesa || ! $currentUser->canAccessDesa($selectedDesa)) {
                    return back()->withInput()->withErrors([
                        'desa_id' => 'Admin Puskesmas hanya dapat menugaskan Bidan dan Kader pada desa di wilayah kerja Puskesmas Anda.',
                    ]);
                }
            } elseif (empty($validated['wilayah_desa'])) {
                return back()->withInput()->withErrors([
                    'desa_id' => 'Wilayah desa penugasan wajib dipilih untuk Bidan atau Kader.',
                ]);
            }
        }

        DB::transaction(function () use (&$validated, $user, $currentUser, $request) {
            // ONLY resolve or create Desa AFTER all validations have passed
            if (empty($validated['desa_id']) && ! empty($validated['wilayah_desa'])) {
                $resolvedDesa = app(WilayahService::class)->resolveOrCreateDesa([
                    'nama' => $validated['wilayah_desa'],
                    'kecamatan' => $validated['wilayah_kecamatan'] ?? ($currentUser->desa?->kecamatan ?? 'Kecamatan Terkait'),
                    'kabupaten' => $validated['wilayah_kabupaten'] ?? ($currentUser->desa?->kabupaten ?? 'Kabupaten Terkait'),
                    'provinsi' => $validated['wilayah_provinsi'] ?? ($currentUser->desa?->provinsi ?? null),
                    'kode_desa' => $validated['wilayah_kode_desa'] ?? null,
                ]);
                if (empty($resolvedDesa->puskesmas) && $currentUser->instansi) {
                    $resolvedDesa->update(['puskesmas' => $currentUser->instansi]);
                }
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
                $allowedDesaIds = Desa::where('kecamatan', $currentUser->desa?->kecamatan)
                    ->orWhere('puskesmas', $currentUser->desa?->puskesmas)
                    ->orWhere('id', $currentUser->desa_id)
                    ->pluck('id')
                    ->all();
                if ($request->filled('desa_id') && in_array((int) $request->input('desa_id'), $allowedDesaIds)) {
                    $validated['desa_id'] = (int) $request->input('desa_id');
                } else {
                    $validated['desa_id'] = $currentUser->desa_id;
                }
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

        if (! $currentUser->canDeleteUser($user)) {
            abort(403, 'Anda tidak memiliki wewenang hierarki untuk menghapus akun pengguna ini.');
        }

        $userName = $user->name;
        $userRole = $user->hierarchy_title;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Akun {$userName} ({$userRole}) berhasil dihapus.");
    }

    /**
     * Toggle active / verification status of a user.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        if (! $currentUser->canToggleStatus($user)) {
            abort(403, 'Anda tidak memiliki hak akses untuk memverifikasi atau mengubah status akun ini.');
        }

        $user->is_active = ! $user->is_active;
        if ($user->is_active && ! $user->phone_verified_at) {
            $user->phone_verified_at = now();
        }
        $user->save();

        $statusText = $user->is_active ? 'diverifikasi dan diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} ({$user->role->label()}) berhasil {$statusText}.");
    }

    /**
     * Resend or regenerate activation link for the given user.
     */
    public function resendActivation(User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        if (! $currentUser->canEditUser($user)) {
            abort(403, 'Anda tidak memiliki hak akses untuk akun ini.');
        }

        $user->generateActivationToken(7);

        if (! empty($user->phone)) {
            try {
                app(WhatsAppService::class)->sendActivationLink($user);
            } catch (\Throwable $e) {
                // Gateway may be offline or unconfigured
            }
        }

        return back()
            ->with('success', 'Tautan aktivasi baru berhasil dibuat untuk '.$user->name.'.')
            ->with('new_user_id', $user->id)
            ->with('new_user_name', $user->name)
            ->with('new_user_phone', $user->phone)
            ->with('new_user_wa_url', $user->phone ? $user->whatsapp_activation_url : null)
            ->with('new_user_activation_url', $user->activation_url);
    }
}
