<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreUserRequest;
use App\Http\Requests\SuperAdmin\UpdateUserRequest;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['roles', 'subscription.plan'])
            ->withTrashed();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->input('role')) {
            $query->role($role);
        }

        // Filter by status
        if ($request->input('status') === 'active') {
            $query->whereNull('deleted_at')->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        }

        // Filter by plan (through subscription)
        if ($planId = $request->input('plan_id')) {
            $query->whereHas('subscription', fn ($q) => $q->where('plan_id', $planId));
        }

        $users = $query->latest()->paginate(25)->withQueryString();
        $plans = Plan::active()->orderBy('name')->get();
        $roles = Role::all();

        return view('super-admin.users.index', compact('users', 'plans', 'roles'));
    }

    public function show(User $user): View
    {
        $user->loadMissing([
            'roles',
            'subscription.plan',
            'links' => fn ($q) => $q->latest()->limit(10),
            'invoices' => fn ($q) => $q->latest()->limit(5),
            'teams',
        ]);

        return view('super-admin.users.show', compact('user'));
    }

    public function create(): View
    {
        $plans = Plan::active()->orderBy('name')->get();
        $roles = Role::all();

        return view('super-admin.users.create', compact('plans', 'roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()
            ->route('super-admin.users.show', $user)
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $user->loadMissing(['roles', 'subscription.plan']);
        $plans = Plan::active()->orderBy('name')->get();
        $roles = Role::all();

        return view('super-admin.users.edit', compact('user', 'plans', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->only('name', 'email', 'is_active', 'timezone', 'locale'));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()
            ->route('super-admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$status} successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->hasRole('super_admin'), 403, 'Cannot delete a super admin.');

        $user->delete();

        return redirect()
            ->route('super-admin.users.index')
            ->with('success', 'User soft-deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', 'User restored successfully.');
    }
}
