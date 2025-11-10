<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;

class AppointmentUpdateController extends Controller
{
  /**
   * Update the specified appointment in storage.
   */
  public function __invoke(StoreAppointmentRequest $request, Appointment $appointment): AppointmentResource
  {
    $appointment->update($request->validated());

    return new AppointmentResource($appointment->fresh());
  }
}
