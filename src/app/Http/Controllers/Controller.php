<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Base controller class.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
