<?php

namespace App\Http\Controllers;

use App\Models\vehicles_characteristics;
use Illuminate\Http\Request;
use App\Models\vehicles;
class VehiclesCharacteristicsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

            $validated = $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',  // Ensure the vehicle exists
                'characteristics' => 'required|array',         // Ensure it's an array
             
            ]);

            // Retrieve the vehicle by ID
            $vehicle = vehicles::find($validated['vehicle_id']);

            if (!$vehicle) {
                return response()->json(['error' => 'Vehicle not found'], 404);
            }

            // Create the relationships
            foreach ($validated['characteristics'] as $characteristic_id) {
                vehicles_characteristics::create([
                    'vehicle_characteristic_vehicle' => $vehicle->id,
                    'vehicle_characteristic_characteristic' => $characteristic_id
                ]);
            }

            return response()->json(['message' => 'Characteristics saved successfully'], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(vehicles_characteristics $vehicles_characteristics)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(vehicles_characteristics $vehicles_characteristics)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, vehicles_characteristics $vehicles_characteristics)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(vehicles_characteristics $vehicles_characteristics)
    {
        //
    }
}
