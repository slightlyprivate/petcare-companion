<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Http\Requests\StorePetRequest;
use Illuminate\Http\Request;

class PetWebController extends Controller
{
  /**
   * Display the pet management page.
   */
  public function index()
  {
    $pets = Pet::latest()->get();

    return view('pets.index', compact('pets'));
  }

  /**
   * Store a new pet via the web interface.
   */
  public function store(StorePetRequest $request)
  {
    Pet::create($request->validated());

    return redirect()->route('pets.index.web')
      ->with('success', 'Pet added successfully!');
  }
}
