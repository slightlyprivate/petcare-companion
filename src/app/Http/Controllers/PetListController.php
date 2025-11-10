<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Http\Resources\PetResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PetListController extends Controller
{
    /**
     * Display a listing of pets.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $query = Pet::query();

        // Apply filters if provided
        if ($request->filled('species')) {
            $query->bySpecies($request->get('species'));
        }

        if ($request->filled('owner_name')) {
            $query->byOwner($request->get('owner_name'));
        }

        if ($request->filled('name')) {
            $query->byName($request->get('name'));
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        $allowedSortFields = ['name', 'species', 'breed', 'owner_name', 'birth_date', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        // Apply pagination
        $perPage = min($request->get('per_page', 15), 50); // Max 50 items per page
        $pets = $query->paginate($perPage);

        return PetResource::collection($pets);
    }
}
