<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;

class AppointmentCreateController extends Controller
{
    /**
     * Store a newly created appointment in storage.
     */
    public function __invoke(StoreAppointmentRequest $request, Pet $pet): JsonResponse
    {
        $appointmentData = $request->validated();
        $appointmentData['pet_id'] = $pet->id; // Set the pet_id from route

        $appointment = Appointment::create($appointmentData);

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }
}
