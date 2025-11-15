<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\User\DeleteUserDataRequest;
use App\Http\Requests\Auth\User\ExportUserDataRequest;
use App\Jobs\DeleteUserDataJob;
use App\Jobs\ExportUserDataJob;
use Illuminate\Http\JsonResponse;

/**
 * Controller for managing user data export and deletion (GDPR compliance).
 *
 * @authenticated
 *
 * @group User Data
 */
class UserDataController extends Controller
{
    /**
     * Request user data export.
     */
    public function exportData(ExportUserDataRequest $request): JsonResponse
    {
        // Dispatch job to export user data
        ExportUserDataJob::dispatch($request->user());

        return response()->json([
            'message' => __('users.export.queued.success'),
            'status' => 'processing',
        ], 202);
    }

    /**
     * Request user data deletion (Right to Erasure).
     */
    public function deleteData(DeleteUserDataRequest $request): JsonResponse
    {
        // Dispatch job to delete user data
        DeleteUserDataJob::dispatch($request->user());

        return response()->json([
            'message' => __('users.delete.queued.success'),
            'status' => 'processing',
        ], 202);
    }
}
