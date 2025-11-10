<?php

namespace App\Http\Controllers;

use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PetAppointmentsController extends Controller
{
    /**
     * Display a listing of appointments for a specific pet.
     */
    public function __invoke(Request $request, Pet $pet): AnonymousResourceCollection|JsonResponse
    {
        $query = Appointment::where('pet_id', $pet->id);

        // Apply status filters if provided
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'upcoming') {
                $query->upcoming();
            } elseif ($status === 'past') {
                $query->past();
            } elseif ($status === 'today') {
                $query->today();
            }
        }

        // Apply date range filters
        if ($request->filled('from_date')) {
            $query->where('scheduled_at', '>=', $request->get('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('scheduled_at', '<=', $request->get('to_date'));
        }

        // Apply search filter for title/notes
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('notes', 'like', '%'.$search.'%');
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'scheduled_at');
        $sortDirection = $request->get('sort_direction', 'asc');

        $allowedSortFields = ['scheduled_at', 'title', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('scheduled_at', 'asc');
        }

        // Apply pagination
        $perPage = min($request->get('per_page', 15), 50); // Max 50 items per page
        $appointments = $query->paginate($perPage);

        return AppointmentResource::collection($appointments);
    }
}
