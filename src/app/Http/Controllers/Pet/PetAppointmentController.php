<?php

namespace App\Http\Controllers\Pet;

use App\Helpers\AppointmentPaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentListRequest;
use App\Http\Requests\AppointmentStoreRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Pet;
use App\Services\Pet\PetAppointmentService;

/**
 * Controller for managing pet appointments.
 */
class PetAppointmentController extends Controller
{
    /** @var PetAppointmentService */
    protected $petAppointmentService;

    /**
     * Create a new controller instance.
     */
    public function __construct(PetAppointmentService $petAppointmentService)
    {
        $this->petAppointmentService = $petAppointmentService;
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(AppointmentStoreRequest $request, Pet $pet): \Illuminate\Http\JsonResponse
    {
        $appointment = $this->petAppointmentService->create($pet, $request->validated());

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a listing of appointments for a specific pet.
     */
    public function index(AppointmentListRequest $request, Pet $pet): \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
    {
        $helper = new AppointmentPaginationHelper($request);

        $appointments = $this->petAppointmentService->list($pet, $helper);

        return AppointmentResource::collection($appointments);
    }
}
