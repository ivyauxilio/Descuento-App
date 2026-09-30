<?php
// app/Services/NotificationBroadcastService.php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationBroadcast;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationBroadcastService
{
    /**
     * Send a broadcast to all matched users.
     */
    public function send(NotificationBroadcast $broadcast, int $chunkSize = 200): int
    {
        if ($broadcast->status === 'sent') {
            throw new \Exception('Broadcast already sent.');
        }

        $broadcast->update(['status' => 'sending']);

        $query = $broadcast->getAudienceQuery();

        // Recipient count
        $totalRecipients = (clone $query)->count();

        if ($totalRecipients === 0) {
            $broadcast->update([
                'status' => 'sent',
                'sent_at' => now(),
                'recipients_count' => 0,
                'delivered_count' => 0,
            ]);
            return 0;
        }

        $delivered = 0;

        try {
            // Process in chunks so we don't blow memory on large audiences
            $query->chunkById($chunkSize, function ($users) use ($broadcast, &$delivered) {
                $rows = [];

                foreach ($users as $user) {
                    $rows[] = [
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'user_id' => $user->id,
                        'broadcast_id' => $broadcast->broadcast_id,
                        'type' => $broadcast->type,
                        'title' => $broadcast->title,
                        'body' => $broadcast->body,
                        'image_url' => $broadcast->image_url,
                        'action_url' => $broadcast->action_url,
                        'action_label' => $broadcast->action_label,
                        'data' => json_encode($broadcast->data),
                        'priority' => $broadcast->priority,
                        'expires_at' => $broadcast->expires_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Bulk insert per chunk
                if (!empty($rows)) {
                    DB::table('notifications')->insert($rows);
                    $delivered += count($rows);
                }
            });

            $broadcast->update([
                'status' => 'sent',
                'sent_at' => now(),
                'recipients_count' => $totalRecipients,
                'delivered_count' => $delivered,
            ]);

            Log::info('Broadcast sent', [
                'broadcast_id' => $broadcast->broadcast_id,
                'recipients' => $totalRecipients,
                'delivered' => $delivered,
            ]);

            return $delivered;

        } catch (\Exception $e) {
            $broadcast->update(['status' => 'failed']);
            Log::error('Broadcast failed', [
                'broadcast_id' => $broadcast->broadcast_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Estimate recipient count without sending.
     */
    public function countRecipients(NotificationBroadcast $broadcast): int
    {
        return $broadcast->getAudienceQuery()->count();
    }

    /**
     * Refresh read stats from notifications.
     */
    public function refreshStats(NotificationBroadcast $broadcast): void
    {
        $readCount = Notification::where('broadcast_id', $broadcast->broadcast_id)
            ->whereNotNull('read_at')
            ->count();

        $broadcast->update(['read_count' => $readCount]);
    }
}