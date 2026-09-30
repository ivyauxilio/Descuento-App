<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications
     * List notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        // Normalize query-string boolean values before validation.
        if ($request->has('unread_only')) {
            $request->merge([
                'unread_only' => filter_var(
                    $request->query('unread_only'),
                    FILTER_VALIDATE_BOOLEAN
                ),
            ]);
        }

        $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'unread_only' => 'nullable|boolean',
            'type' => 'nullable|string|max:50',
        ]);

        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $query = Notification::forUser($user->id)
                ->notExpired()
                ->latestFirst();

            // Filter by unread
            if ($request->boolean('unread_only')) {
                $query->unread();
            }

            // Filter by type
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            $perPage = (int) ($request->per_page ?? 20);

            $notifications = $query->paginate($perPage);

            $items = collect($notifications->items())->map(function ($n) {
                return $this->transform($n);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $items,
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
                'unread_count' => Notification::forUser($user->id)
                    ->notExpired()
                    ->unread()
                    ->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Notification index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load notifications.',
            ], 500);
        }
    }
    /**
     * GET /api/notifications/unread-count
     */
    public function unreadCount()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $count = Notification::forUser($user->id)
                ->notExpired()
                ->unread()
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Unread count error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load unread count.',
            ], 500);
        }
    }

    /**
     * POST /api/notifications/{id}/read
     * Mark a single notification as read.
     */
    public function markAsRead($id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // Accept either numeric ID or UUID
            $notification = Notification::forUser($user->id)
                ->where(function ($q) use ($id) {
                    $q->where('id', $id)
                      ->orWhere('uuid', $id);
                })
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found.',
                ], 404);
            }

            $marked = $notification->markAsRead();

            return response()->json([
                'success' => true,
                'data' => $this->transform($notification->fresh()),
                'marked' => $marked,
                'message' => $marked ? 'Marked as read.' : 'Already read.',
            ]);

        } catch (\Exception $e) {
            Log::error('Mark as read error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read.',
            ], 500);
        }
    }

    /**
     * POST /api/notifications/read-all
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $count = Notification::forUser($user->id)
                ->notExpired()
                ->unread()
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'data' => [
                    'marked_count' => $count,
                ],
                'message' => "Marked {$count} notifications as read.",
            ]);

        } catch (\Exception $e) {
            Log::error('Mark all as read error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all as read.',
            ], 500);
        }
    }

    /**
     * DELETE /api/notifications/{id}
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $notification = Notification::forUser($user->id)
                ->where(function ($q) use ($id) {
                    $q->where('id', $id)
                      ->orWhere('uuid', $id);
                })
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found.',
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted.',
            ]);

        } catch (\Exception $e) {
            Log::error('Delete notification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification.',
            ], 500);
        }
    }

    /**
     * GET /api/notifications/{id}
     * (Optional) Show a single notification — useful for deep-link modal views.
     */
    public function show($id)
    {
        try {
            $user = auth()->user();

            $notification = Notification::forUser($user->id)
                ->where(function ($q) use ($id) {
                    $q->where('id', $id)
                      ->orWhere('uuid', $id);
                })
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found.',
                ], 404);
            }

            // Auto-mark as read on view
            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'data' => $this->transform($notification->fresh()),
            ]);

        } catch (\Exception $e) {
            Log::error('Show notification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load notification.',
            ], 500);
        }
    }

    /**
     * DELETE /api/notifications
     * Clear all notifications.
     */
    public function clearAll(Request $request)
    {
        try {
            $user = auth()->user();

            // Optional: only clear read notifications
            $onlyRead = $request->boolean('only_read', false);

            $query = Notification::forUser($user->id);

            if ($onlyRead) {
                $query->read();
            }

            $count = $query->delete();

            return response()->json([
                'success' => true,
                'message' => "Cleared {$count} notifications.",
            ]);

        } catch (\Exception $e) {
            Log::error('Clear notifications error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear notifications.',
            ], 500);
        }
    }

    /**
     * Transform a notification into the payload the app expects.
     */
    private function transform(Notification $n): array
    {
        return [
            'id' => $n->id,
            'uuid' => $n->uuid,
            'type' => $n->type,
            'title' => $n->title,
            'body' => $n->body,
            'message' => $n->body, // alias for app compatibility
            'image_url' => $n->image_url,
            'action_url' => $n->action_url,
            'action_label' => $n->action_label,
            'data' => $n->data,
            'priority' => $n->priority,
            'read_at' => $n->read_at?->toIso8601String(),
            'is_read' => $n->isRead(),
            'expires_at' => $n->expires_at?->toIso8601String(),
            'created_at' => $n->created_at->toIso8601String(),
            'time_ago' => $n->created_at->diffForHumans(),
        ];
    }
}