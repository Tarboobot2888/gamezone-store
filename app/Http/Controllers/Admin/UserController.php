<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Admin\UserRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('role') && $request->role !== '') {
            $query->role($request->role);
        }

        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'verified') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $roles = Role::all();

        if ($request->expectsJson()) {
            return response()->json($users);
        }

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function show(User $user)
    {
        $user->load(['roles', 'orders', 'supportTickets']);

        if (request()->expectsJson()) {
            return response()->json($user);
        }

        return view('admin.users.show', compact('user'));
    }

    public function store(UserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'email_verified_at' => $request->email_verified ? now() : null,
        ]);

        if ($request->role) {
            $user->assignRole($request->role);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('User created successfully'),
                'user' => $user->load('roles')
            ], 201);
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('User created successfully'));
    }

    public function update(UserRequest $request, User $user)
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->has('email_verified')) {
            $data['email_verified_at'] = $request->email_verified ? now() : null;
        }

        $user->update($data);

        if ($request->role) {
            $user->syncRoles([$request->role]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('User updated successfully'),
                'user' => $user->load('roles')
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('User updated successfully'));
    }

    public function destroy(User $user)
    {
        // Prevent deleting super admin
        if ($user->hasRole('super-admin')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cannot delete super admin user')
                ], 403);
            }

            return redirect()->route('admin.users.index')
                ->with('error', __('Cannot delete super admin user'));
        }

        $user->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('User deleted successfully')
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('User deleted successfully'));
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'email_verified_at' => $user->email_verified_at ? null : now()
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('User status updated successfully')
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('User status updated successfully'));
    }

    public function impersonate(User $user)
    {
        if ($user->hasRole('super-admin')) {
            return redirect()->route('admin.users.index')
                ->with('error', __('Cannot impersonate super admin'));
        }

        session(['impersonating' => auth()->id()]);
        auth()->login($user);

        return redirect()->route('dashboard.index')
            ->with('info', __('You are now impersonating :name', ['name' => $user->name]));
    }

    public function stopImpersonating()
    {
        if (!session('impersonating')) {
            return redirect()->route('dashboard.index');
        }

        $originalUserId = session('impersonating');
        session()->forget('impersonating');
        
        auth()->loginUsingId($originalUserId);

        return redirect()->route('admin.users.index')
            ->with('success', __('Stopped impersonating user'));
    }
}
