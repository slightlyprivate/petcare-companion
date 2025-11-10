<?php

namespace App\Http\Controllers;

use App\Http\Requests\PetStoreRequest;
use App\Models\Pet;
use Illuminate\Support\Facades\Auth;

/**
 * Web controller for managing pets.
 */
class PetWebController extends Controller
{
    /**
     * Display the pet management page.
     */
    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $pets = Pet::latest()->get();

        return view('pets.index', compact('pets'));
    }

    /**
     * Store a new pet via the web interface.
     */
    public function store(PetStoreRequest $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        Pet::create($request->validated());

        return redirect()->route('pets.index.web')
            ->with('success', 'Pet added successfully!');
    }
}
