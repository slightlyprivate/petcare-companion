<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class AppointmentDirectCreateController extends Controller
{
    /**
     * Store a newly created appointment in storage.
     */
    public function __invoke(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = Appointment::create($request->validated());

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }
}
