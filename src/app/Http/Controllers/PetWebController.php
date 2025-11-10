<?php

namespace App\Http\Controllers;

use App\Http\Requests\PetStoreRequest;
use App\Models\Pet;

/**
 * Web controller for managing pets.
 */
class PetWebController extends Controller
{
    /**
     * Display the pet management page.
     */
    public function index(): \Illuminate\View\View
    {
        $pets = Pet::latest()->get();

        return view('pets.index', compact('pets'));
    }

    /**
     * Store a new pet via the web interface.
     */
    public function store(PetStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        Pet::create($request->validated());

        return redirect()->route('pets.index.web')
            ->with('success', 'Pet added successfully!');
    }
}
