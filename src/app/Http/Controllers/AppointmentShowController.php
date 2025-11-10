<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Request;

class AppointmentShowController extends Controller
{
  /**
   * Display the specified appointment.
   */
  public function __invoke(Appointment $appointment, Request $request): AppointmentResource
  {
    // Load pet if requested
    if ($request->query('include') === 'pet') {
      $appointment->load('pet');
    }

    return new AppointmentResource($appointment);
  }
}
