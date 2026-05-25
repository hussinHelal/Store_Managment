<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppNotificationController extends Controller
{
    public function list()
    {
        $notifications = AppNotification::active()->latest()->get();
        return view('notifications.list', compact('notifications'));
    }

    public function markAllRead()
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        try {
            $activeIds = AppNotification::active()->pluck('id');
            foreach ($activeIds as $nid) {
                \App\Models\NotificationRead::updateOrCreate(
                    ['user_id' => $user->id, 'app_notification_id' => $nid],
                    ['read_at' => now()]
                );
            }
        } catch (\Exception $e) {
        }

        return redirect()->route('notifications.list');
    }

    public function markRead($id)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        try {
            \App\Models\NotificationRead::updateOrCreate(
                ['user_id' => $user->id, 'app_notification_id' => $id],
                ['read_at' => now()]
            );
        } catch (\Exception $e) {
        }

        return redirect()->route('notifications.list');
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user || !($user->isAdmin() || $user->isSuperAdmin())) {
            abort(403);
        }

        $notifications = AppNotification::latest()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user || !($user->isAdmin() || $user->isSuperAdmin())) {
            abort(403);
        }

        return view('notifications.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !($user->isAdmin() || $user->isSuperAdmin())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = $user->id;

        AppNotification::create($validated);

        return redirect()->route('admin.notifications.index')->with('success', 'تم إنشاء التنبيه بنجاح.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || !($user->isAdmin() || $user->isSuperAdmin())) {
            abort(403);
        }

        $notification = AppNotification::findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'تم حذف التنبيه.');
    }
}
