<?php

namespace App\Modules\Communication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Security\Models\SecurityNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | API – Unread badge (sidebar/header)
    |--------------------------------------------------------------------------
    */

    /**
     * Return recent unread notifications for the header/sidebar badge.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->scopedQuery($request)->whereNull('read_at');
        
        $notifications = $query->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'count' => $query->count(),
            'data'  => $notifications,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Full-page list
    |--------------------------------------------------------------------------
    */

    /**
     * Display the paginated notification list with filters and stats.
     */
    public function all(Request $request): View
    {
        $query = $this->scopedQuery($request)->latest();

        // Filter: notification type
        if ($request->filled('type')) {
            $data = $request->validate(['type' => 'in:info,warning,error,success']);
            $query->where('type', $data['type']);
        }

        // Filter: read status
        if ($request->filled('read')) {
            match ($request->read) {
                'unread' => $query->whereNull('read_at'),
                'read'   => $query->whereNotNull('read_at'),
                default  => null,
            };
        }

        // Filter: full-text search across title, message, ip_address, event
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('message', 'like', "%{$term}%")
                  ->orWhere('event', 'like', "%{$term}%")
                  ->orWhere('ip_address', 'like', "%{$term}%");
            });
        }

        $notifications = $query->paginate(20)->withQueryString();

        // Stats – cached counts for performance on large tables
        $stats = [
            'total'   => (clone $query)->count(),
            'unread'  => (clone $query)->whereNull('read_at')->count(),
            'warning' => (clone $query)->where('type', 'warning')->count(),
            'error'   => (clone $query)->where('type', 'error')->count(),
        ];

        return view('communication::notifications.index', compact('notifications', 'stats'));
    }

    /*
    |--------------------------------------------------------------------------
    | API – Mark read / delete
    |--------------------------------------------------------------------------
    */

    /**
     * Mark all unread notifications as read.
     */
    public function markAsRead(): JsonResponse
    {
        $query = SecurityNotification::query();

        if (! request()->user()->can('access-admin-security')) {
            $query->where('user_id', request()->user()->id);
        }

        $updated = $query->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markOneRead(SecurityNotification $notification): JsonResponse
    {
        $this->authorize('update', $notification);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Permanently delete a notification.
     */
    public function delete(SecurityNotification $notification): JsonResponse
    {
        $this->authorize('delete', $notification);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    private function scopedQuery(Request $request)
    {
        return SecurityNotification::query()
            ->when(
                ! $request->user()->can('access-admin-security'),
                fn ($query) => $query->where('user_id', $request->user()->id)
            );
    }
}
