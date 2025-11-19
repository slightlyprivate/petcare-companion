<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Models\UserExport;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL as UrlFacade;
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
        // Check if export is still within validity window
        if ($export->expires_at->isPast()) {
            return response()->json([
                'message' => 'This export link has expired. Please request a new data export.',
                'status' => 'expired',
            ], 410);
        }

        // Validate the signature only after checking expiry so we can return 410 for expired links
        if (! UrlFacade::hasValidSignature($request)) {
            return response()->json([
                'message' => 'Invalid or tampered link.',
            ], 403);
        }

        // Resolve user via Sanctum guard (without relying on middleware)
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Verify the user owns this export
        if ($export->user_id !== $user->id) {
            throw new AuthorizationException('You cannot access this export.');
        }

        $disk = Storage::disk('exports');

        // Verify file exists
        if (! $disk->exists($export->file_path)) {
            return response()->json([
                'message' => 'Export file not found.',
                'status' => 'not_found',
            ], 404);
        }
        // Mark as downloaded
        $export->markAsDownloaded();

        // Return as standard response with headers so test helpers can inspect headers
        $content = $disk->get($export->file_path);

        return response(
            $content,
            200,
            [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $export->file_name . '"',
            ]
        );
    }
}
