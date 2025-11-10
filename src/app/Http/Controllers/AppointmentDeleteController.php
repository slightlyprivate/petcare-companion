<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Response;

class AppointmentDeleteController extends Controller
{
  /**
   * Remove the specified appointment from storage.
   */
  public function __invoke(Appointment $appointment): Response
  {
    $appointment->delete();

    return response()->noContent();
  }
}
