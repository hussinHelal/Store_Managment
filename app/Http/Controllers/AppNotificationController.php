<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppNotificationController extends Controller
{
    public function list()
    {
        $notifications = AppNotification::active()->latest()->paginate(20);

        if (auth()->check()) {
            $user = auth()->user();
            $reads = \App\Models\NotificationRead::where('user_id', $user->id)
                ->whereIn('app_notification_id', $notifications->getCollection()->pluck('id'))
                ->pluck('app_notification_id')->all();

            $notifications->transform(function ($notification) use ($reads) {
                $notification->is_read = in_array($notification->id, $reads, true);
                return $notification;
            });
        } else {
            $notifications->transform(function ($notification) {
                $notification->is_read = false;
                return $notification;
            });
        }

        return view('notifications.list', compact('notifications'));
    }

    public function markAllRead()
    {
        $user = auth()->user();
        $timestamp = now();
        $reads = AppNotification::active()->pluck('id')->map(fn ($id) => [
            'user_id' => $user->id,
            'app_notification_id' => $id,
            'read_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->all();

        if ($reads !== []) {
            \App\Models\NotificationRead::upsert(
                $reads,
                ['user_id', 'app_notification_id'],
                ['read_at', 'updated_at']
            );
        }

        return redirect()->route('notifications.list');
    }

    public function markRead($id)
    {
        $user = auth()->user();
        $notification = AppNotification::active()->findOrFail($id);

        \App\Models\NotificationRead::updateOrCreate(
            ['user_id' => $user->id, 'app_notification_id' => $notification->id],
            ['read_at' => now()]
        );

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'تم وضع الإشعار كمقروء.']);
        }

        return redirect()->route('notifications.list');
    }

    public function index()
    {
        $notifications = AppNotification::latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('notifications.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = $user->id;

        AppNotification::create($validated);

        return redirect()->route('admin.notifications.index')->with('success', 'تم إنشاء التنبيه بنجاح.');
    }

    public function destroy($id)
    {
        $notification = AppNotification::findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'تم حذف التنبيه.');
    }
}
