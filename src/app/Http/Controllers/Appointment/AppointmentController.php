<?php

namespace App\Http\Controllers\Appointment;

use App\Helpers\AppointmentPaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentListRequest;
use App\Http\Requests\AppointmentShowRequest;
use App\Http\Requests\AppointmentStoreRequest;
use App\Http\Requests\AppointmentUpdateRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\Appointment\AppointmentService;

/**
 * Controller for managing appointments.
 *
 * @group Appointments
 */
class AppointmentController extends Controller
{
    /** @var AppointmentService */
    protected $appointmentService;

    /**
     * Create a new controller instance.
     */
    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(AppointmentStoreRequest $request)
    {
        $appointment = $this->appointmentService->create($request->validated());

        return new AppointmentResource($appointment);
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(AppointmentUpdateRequest $request, Appointment $appointment)
    {
        $appointment = $this->appointmentService->update($appointment, $request->validated());

        return new AppointmentResource($appointment);
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(Appointment $appointment): \Illuminate\Http\Response
    {
        $this->appointmentService->delete($appointment);

        return response()->noContent();
    }

    /**
     * Get a listing of all appointments.
     */
    public function index(AppointmentListRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $helper = new AppointmentPaginationHelper($request);

        $appointments = $this->appointmentService->list($helper);

        return AppointmentResource::collection($appointments);
    }

    /**
     * Get the specified appointment.
     */
    public function show(AppointmentShowRequest $request, Appointment $appointment): AppointmentResource
    {
        $resource = $this->appointmentService->findById($appointment->id);
        // Load related pet if requested
        if ($request->has('include') && $request->get('include') === 'pet') {
            $resource->load('pet');
        }

        return new AppointmentResource($resource);
    }
}
