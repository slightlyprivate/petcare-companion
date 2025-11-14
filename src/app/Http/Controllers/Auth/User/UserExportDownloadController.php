<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Models\UserExport;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for managing user data export downloads.
 *
 * @authenticated
 *
 * @group User Data
 */
class UserExportDownloadController extends Controller
{
    /**
     * Download user data export file using signed URL.
     *
     * This endpoint validates the signed URL and serves the ZIP file to the authenticated user.
     * The URL expires 7 days after export generation.
     *
     *
     * @return StreamedResponse
     *
     * @throws AuthorizationException if user doesn't own the export or link has expired
     */
    public function download(Request $request, UserExport $export)
    {
        // Verify the user owns this export
        if ($export->user_id !== $request->user()->id) {
            throw new AuthorizationException('You cannot access this export.');
        }

        // Check if export is still within validity window
        if ($export->expires_at->isPast()) {
            return response()->json([
                'message' => 'This export link has expired. Please request a new data export.',
                'status' => 'expired',
            ], 410);
        }

        $disk = Storage::disk('local');

        // Verify file exists
        if (! $disk->exists($export->file_path)) {
            return response()->json([
                'message' => 'Export file not found.',
                'status' => 'not_found',
            ], 404);
        }
        // Mark as downloaded
        $export->markAsDownloaded();

        $filePath = $disk->path($export->file_path);

        // Stream the file to the user
        return response()->download(
            $filePath,
            $export->file_name,
            ['Content-Type' => 'application/zip']
        );
    }
}
