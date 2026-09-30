<?php
// app/Http/Controllers/Admin/NotificationBroadcastController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationBroadcast;
use App\Models\User;
use App\Services\NotificationBroadcastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationBroadcastController extends Controller
{
    protected NotificationBroadcastService $broadcaster;

    public function __construct(NotificationBroadcastService $broadcaster)
    {
        $this->broadcaster = $broadcaster;
    }

    /**
     * List all broadcasts
     */
    public function index(Request $request)
    {
        $query = NotificationBroadcast::with('sender')
            ->orderByDesc('created_at');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('body', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $broadcasts = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => NotificationBroadcast::count(),
            'sent' => NotificationBroadcast::where('status', 'sent')->count(),
            'drafts' => NotificationBroadcast::where('status', 'draft')->count(),
            'total_recipients' => NotificationBroadcast::where('status', 'sent')->sum('recipients_count'),
            'total_reads' => NotificationBroadcast::sum('read_count'),
        ];

        return view('admin.broadcasts.index', compact('broadcasts', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.broadcasts.create');
    }

    /**
     * Store new broadcast
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string|max:2000',
            'type' => 'required|string|max:50',
            'image_url' => 'nullable|url|max:500',
            'action_url' => 'nullable|string|max:500',
            'action_label' => 'nullable|string|max:50',
            'priority' => 'required|in:low,normal,high,urgent',
            'audience' => 'required|in:all,customers,merchants,active,inactive,has_wallet,has_referrals,custom',
            'custom_user_ids' => 'nullable|array',
            'custom_user_ids.*' => 'exists:users,id',
            'expires_at' => 'nullable|date|after:now',
            'send_now' => 'nullable|boolean',
        ]);

        $broadcast = NotificationBroadcast::create([
            'broadcast_uuid' => (string) Str::uuid(),
            'sent_by' => auth()->id(),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'action_url' => $validated['action_url'] ?? null,
            'action_label' => $validated['action_label'] ?? null,
            'priority' => $validated['priority'],
            'audience' => $validated['audience'],
            'custom_user_ids' => $validated['custom_user_ids'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'status' => 'draft',
        ]);

        // ✅ Send immediately if requested
        if ($request->boolean('send_now')) {
            try {
                $count = $this->broadcaster->send($broadcast);
                return redirect()
                    ->route('admin.broadcasts.show', $broadcast->broadcast_id)
                    ->with('success', "Broadcast sent to {$count} users.");
            } catch (\Exception $e) {
                return redirect()
                    ->route('admin.broadcasts.show', $broadcast->broadcast_id)
                    ->with('error', 'Broadcast failed: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.broadcasts.show', $broadcast->broadcast_id)
            ->with('success', 'Broadcast saved as draft.');
    }

    /**
     * Show a broadcast
     */
    public function show($id)
    {
        $broadcast = NotificationBroadcast::with('sender')
            ->findOrFail($id);

        // Refresh read count from notifications table
        $this->broadcaster->refreshStats($broadcast);

        return view('admin.broadcasts.show', compact('broadcast'));
    }

    /**
     * Send an existing draft
     */
    public function send($id)
    {
        $broadcast = NotificationBroadcast::findOrFail($id);

        if ($broadcast->status === 'sent') {
            return back()->with('error', 'Broadcast already sent.');
        }

        try {
            $count = $this->broadcaster->send($broadcast);
            return redirect()
                ->route('admin.broadcasts.show', $broadcast->broadcast_id)
                ->with('success', "Broadcast sent to {$count} users.");
        } catch (\Exception $e) {
            return back()->with('error', 'Broadcast failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a broadcast (only draft or failed)
     */
    public function destroy($id)
    {
        $broadcast = NotificationBroadcast::findOrFail($id);

        if ($broadcast->status === 'sent') {
            return back()->with('error', 'Cannot delete a sent broadcast. It has already been delivered.');
        }

        $broadcast->delete();

        return redirect()
            ->route('admin.broadcasts.index')
            ->with('success', 'Broadcast deleted.');
    }

    /**
     * AJAX: preview audience count
     */
    public function previewAudience(Request $request)
    {
        $request->validate([
            'audience' => 'required|in:all,customers,merchants,active,inactive,has_wallet,has_referrals,custom',
            'custom_user_ids' => 'nullable|array',
        ]);

        $tempBroadcast = new NotificationBroadcast([
            'audience' => $request->audience,
            'custom_user_ids' => $request->custom_user_ids ?? null,
        ]);

        $count = $tempBroadcast->getAudienceQuery()->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * AJAX: search users for the "custom" audience
     */
    public function searchUsers(Request $request)
    {
        $q = $request->get('q', '');

        $users = User::query()
            ->where(function ($query) use ($q) {
                $query->where('firstname', 'LIKE', "%{$q}%")
                      ->orWhere('lastname', 'LIKE', "%{$q}%")
                      ->orWhere('email', 'LIKE', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'firstname', 'lastname', 'email', 'role']);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }
}