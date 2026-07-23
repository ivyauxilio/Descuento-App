<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate(15);
        $roles = ['admin', 'merchant', 'customer'];
        $statuses = ['active', 'inactive', 'suspended'];

        return view('admin.users.index', compact('users', 'roles', 'statuses'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = ['admin', 'merchant', 'customer'];
        $statuses = ['active', 'inactive', 'suspended'];

        return view('admin.users.create', compact('roles', 'statuses'));
    }

    /**
     * Store a newly created user.
     */
    public function store(UserRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => $request->status,
                'email_verified_at' => $request->has('email_verified') ? now() : null,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = ['admin', 'merchant', 'customer'];
        $statuses = ['active', 'inactive', 'suspended'];

        return view('admin.users.edit', compact('user', 'roles', 'statuses'));
    }

    /**
     * Update the specified user.
     */
    public function update(UserRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            $data = [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => $request->status,
                'email_verified_at' => $request->has('email_verified') ? now() : null,
            ];

            // Update password only if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                return back()->with('error', 'You cannot delete your own account.');
            }

            $user->delete();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        try {
            // Prevent deleting yourself
            $ids = array_filter($request->ids, function ($id) {
                return $id !== auth()->id();
            });

            if (empty($ids)) {
                return back()->with('error', 'You cannot delete your own account.');
            }

            User::whereIn('id', $ids)->delete();

            return redirect()
                ->route('admin.users.index')
                ->with('success', count($ids) . ' users deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete users: ' . $e->getMessage());
        }
    }

    /**
     * Update user status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required', 'in:active,inactive,suspended'],
        ]);

        try {
            $user = User::findOrFail($id);

            // Prevent changing your own status
            if ($user->id === auth()->id()) {
                return back()->with('error', 'You cannot change your own status.');
            }

            $user->status = $request->status;
            $user->save();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User status updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Toggle email verification.
     */
    public function toggleVerification(string $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->email_verified_at) {
                $user->email_verified_at = null;
                $message = 'Email verification removed.';
            } else {
                $user->email_verified_at = now();
                $message = 'Email verified successfully.';
            }

            $user->save();

            return redirect()
                ->route('admin.users.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to toggle verification: ' . $e->getMessage());
        }
    }
}