<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateNotificationPreferenceRequest;
use App\Services\Auth\Notifications\NotificationPreferencesService;
use App\Support\Messages\NotificationsMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller for managing user notification preferences.
 *
 * @group Notification Preferences
 */
class NotificationPreferenceController extends Controller
{
    /** @var NotificationPreferencesService */
    protected $notificationPreferencesService;

    /**
     * Create a new controller instance.
     */
    public function __construct(NotificationPreferencesService $notificationPreferencesService)
    {
        $this->notificationPreferencesService = $notificationPreferencesService;
    }

    /**
     * Get the authenticated user's notification preferences.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $preferences = $this->notificationPreferencesService
            ->getUserPreferences($userId)
            ->toArray();

        return response()->json([
            'data' => $preferences,
        ]);
    }

    /**
     * Update a specific notification preference type.
     */
    public function update(UpdateNotificationPreferenceRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $type = $request->input('type');
        $enabled = $request->input('enabled');

        try {
            $this->notificationPreferencesService->updateUserPreference($userId, $type, $enabled);
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            Log::error($e);

            return response()->json([
                'message' => NotificationsMessages::notificationPreferenceUpdateFailed(),
            ], 500);
        }

        return response()->json([
            'message' => NotificationsMessages::notificationPreferenceUpdated(),
            'data' => [
                'type' => $type,
                'enabled' => $enabled,
            ],
        ]);
    }

    /**
     * Disable all notifications for the authenticated user.
     */
    public function disableAll(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $preferences = $this->notificationPreferencesService
            ->getUserPreferences($userId);

        $preferences->disableAll();

        return response()->json([
            'message' => NotificationsMessages::notificationsDisabledSuccessfully(),
        ]);
    }

    /**
     * Enable all notifications for the authenticated user.
     */
    public function enableAll(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $preferences = $this->notificationPreferencesService
            ->getUserPreferences($userId);

        $preferences->enableAll();

        return response()->json([
            'message' => NotificationsMessages::notificationsEnabledSuccessfully(),
        ]);
    }
}
