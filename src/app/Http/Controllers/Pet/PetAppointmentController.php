<?php

namespace App\Http\Controllers\Pet;

use App\Helpers\AppointmentPaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\ListAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Models\Pet;
use App\Services\Pet\PetAppointmentService;

/**
 * Controller for managing pet appointments.
 *
 * @authenticated
 *
 * @group Pets
 *
 * @subgroup Appointments
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
    public function store(StoreAppointmentRequest $request, Pet $pet): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $pet);

        $appointment = $this->petAppointmentService->create($request->user(), $pet, $request->validated());

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a listing of appointments for a specific pet.
     */
    public function index(ListAppointmentRequest $request, Pet $pet): \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $pet);

        $helper = new AppointmentPaginationHelper($request);

        $appointments = $this->petAppointmentService->list($pet, $helper);

        return AppointmentResource::collection($appointments);
    }
}
