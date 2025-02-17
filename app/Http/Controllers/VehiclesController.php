<?php

namespace App\Http\Controllers;

use App\Models\vehicles;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Support\Facades\Log;

use App\Models\Categories;
use App\Models\brands;
use App\Models\characteristics;
use App\Models\models;
use App\Models\ecus;
use App\Models\engines;
use App\Models\generations;
use App\Models\vehicle_chart_data;
use App\Models\vehicles_characteristics;
use App\Models\tuning;
use App\Models\vehicle_tuning;
use Exception;
use Illuminate\Http\JsonResponse;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;



class VehiclesController extends Controller
{

    public function getVehiclesInfo()
    {
        $categories = Categories::all();
        $brands = brands::all();
        $models = models::all();
        $ecus = ecus::all();
        $engines = engines::all();
        $generations = generations::all();

        $brands = brands::all();
        $models = models::all();
        $ecus = ecus::all();
        $engines = engines::all();
        $generations = generations::all();
        $tuning = tuning::all();
        $characteristics=characteristics::all();



        return response()->json( [  "categories" => $categories,
        "brands" => $brands,
        "models" => $models,
        "ecus" => $ecus,
        "engines" => $engines,
        "generations" => $generations,
        "tuning"=>$tuning,
        "characteristics"=>$characteristics])  ;
       }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $categories = Categories::all();
        $brands = brands::all();
        $models = models::all();
        $ecus = ecus::all();
        $engines = engines::all();
        $generations = generations::all();
        $categories = categories::all();
        $brands = brands::all();
        $models = models::all();
        $ecus = ecus::all();
        $engines = engines::all();
        $generations = generations::all();


        return view("vehicles.index", [
            "categories" => $categories,
            "brands" => $brands,
            "models" => $models,
            "ecus" => $ecus,
            "engines" => $engines,
            "generations" => $generations,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */


    public function create()
    {
        //
    }
    public function getVehicleNames(Request $request)
    {
        $categoryId = $request->get('category_id');

        $vehicles = vehicles::where('category_id', $categoryId)
                        ->pluck("brand_id")
                        ->unique();

        $brands = brands::whereIn("brand_id", $vehicles)->get();

        return response()->json($brands);
    }

    public function getVehicleCategories()
    {
        // Fetch all vehicle categories
        $categories = Categories::all();

        return response()->json($categories);
    }


    public function getVehicleDetails(Request $request) {

        $validated = $request->validate([
            'category_id' => 'required',
            'brand_id' => 'required',
            'model_id' => 'required',
            'generation_id' => 'required',
            'engine_id' => 'required',
            'ecu_id' => 'required',
        ]);

        $vehicle = vehicles::where('category_id', $validated['category_id'])
            ->where('brand_id', $validated['brand_id'])
            ->where('model_id', $validated['model_id'])
            ->where('generation_id', $validated['generation_id'])
            ->where('engine_id', $validated['engine_id'])
            ->where('ecu_id', $validated['ecu_id'])
            ->with(["brand","model","generation","engine","ecu","data_chart","vehicle_tuning","tuning"])
            ->first();

        $vehicle_characteristics = vehicles_characteristics::where("vehicle_characteristic_vehicle", $vehicle["id"])
                                ->pluck("vehicle_characteristic_characteristic")
                                ->unique();

        $characteristics = characteristics::whereIn("characteristic_id", $vehicle_characteristics)->get();

        if (!$vehicle) {
            return response()->json(['message' => 'No vehicle found with the specified criteria.'], 404);
        }

        return response()->json([$vehicle,$characteristics]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {


        $validated = $request->validate([
            // vehicle details
            "vehicle_name" => "required|string|max:255",
            "vehicle_fuel" => "required|string|max:255",
            "vehicle_category" => "required|exists:categories,category_id",
            "vehicle_model" => "required|exists:models,model_id",
            "vehicle_brand" => "required|exists:brands,brand_id",
            "vehicle_engine" => "required|exists:engines,engine_id",
            "vehicle_ecu" => "required|exists:ecus,ecu_id",
            "vehicle_generation" => "required|exists:generations,generation_id",
            "vehicle_standard_power" => "required|numeric",
            "vehicle_standard_torque" => "required|numeric",
            "vehicle_cylinder" => "required|string|max:255",
            "vehicle_compression" => "required|string|max:255",
            "vehicle_bore" => "required|string|max:255",

            // tuning type
            "vehicle_tuning" => "required|numeric",

            // vehicle chart data
            "vehicle_data_rpm" => "required|string",
            "vehicle_data_oem_power_chart" => "required|string",
            "vehicle_data_oem_torque_chart" => "required|string",

            // vehicle tuning details
            "vehicle_tuning_difference_power" => "required|numeric",
            "vehicle_tuning_difference_torque" => "required|numeric",
            "vehicle_tuning_max_power" => "required|numeric",
            "vehicle_tuning_max_torque" => "required|numeric",
            "vehicle_tuning_power_chart" => "required|string",
            "vehicle_tuning_torque_chart" => "required|string",
        ]);

        DB::beginTransaction();

        $chart_data = vehicle_chart_data::firstOrCreate([
            "vehicle_data_rpm" => $validated["vehicle_data_rpm"],
            "vehicle_data_oem_power_chart" => $validated["vehicle_data_oem_power_chart"],
            "vehicle_data_oem_torque_chart" => $validated["vehicle_data_oem_torque_chart"],
        ]);

        $vehicle_tuning_details = vehicle_tuning::firstOrCreate([
            "vehicle_tuning_difference_power" => $validated["vehicle_tuning_difference_power"],
            "vehicle_tuning_difference_torque" => $validated["vehicle_tuning_difference_torque"],
            "vehicle_tuning_max_power" => $validated["vehicle_tuning_max_power"],
            "vehicle_tuning_max_torque" => $validated["vehicle_tuning_max_torque"],
            "vehicle_tuning_power_chart" => $validated["vehicle_tuning_power_chart"],
            "vehicle_tuning_torque_chart" => $validated["vehicle_tuning_torque_chart"]
        ]);

        $vehicle = vehicles::create([
            "vehicle_name" => $validated["vehicle_name"],
            "vehicle_fuel" => $validated["vehicle_fuel"],
            "vehicle_standard_power" => $validated["vehicle_standard_power"],
            "vehicle_standard_torque" => $validated["vehicle_standard_torque"],
            "vehicle_cylinder" => $validated["vehicle_cylinder"],
            "vehicle_compression" => $validated["vehicle_compression"],
            "vehicle_bore" => $validated["vehicle_bore"],
            "generation_id" => $validated["vehicle_generation"],
            "category_id" => $validated["vehicle_category"],
            "model_id" => $validated["vehicle_model"],
            "brand_id" => $validated["vehicle_brand"],
            "engine_id" => $validated["vehicle_engine"],
            "ecu_id" => $validated["vehicle_ecu"],
            "vehicle_tuning" => $validated["vehicle_tuning"],
            "vehicle_tuning_details" => $vehicle_tuning_details->vehicle_tuning_id,
            "data_chart_id" => $chart_data->vehicle_data_id
        ]);


        DB::commit();

        return response()->json([
            'message' => 'Vehicle created successfully',
            'vehicle_id' => $vehicle->id
        ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(), // Returns detailed validation errors.
            ], 422);
        } catch (QueryException $e) { // Handles database-related errors.
            DB::rollBack();
            return response()->json([
                'message' => 'Database error occurred',
                'errors' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'sql' => $e->getSql(), // Only use getSql() if using Laravel's QueryException.
                    'bindings' => $e->getBindings(),
                ],
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An unexpected error occurred',
                'errors' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(), // Full stack trace for debugging.
                ],
            ], 500);
        }

    }

    public function getVehicleDetailsDropdown(Request $request)
    {
        $vehicle_name = $request->input('vehicle_name');

        $vehicles = Vehicles::where('vehicle_name', $vehicle_name)
            ->with(['model', 'brand', 'engine', ])
            ->get();

        $models = $vehicles->pluck('model.model_name', 'model_id')->unique();
        $brands = $vehicles->pluck('brand.brand_name', 'brand_id')->unique();
        $engines = $vehicles->pluck('engine.engine_name', 'engine_id')->unique();

        return response()->json([
            'models' => view('partials.vehicle_models', compact('models'))->render(),
            'brands' => view('partials.vehicle_brands', compact('brands'))->render(),
            'engines' => view('partials.vehicle_engines', compact('engines'))->render(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(vehicles $vehicles)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(vehicles $vehicles)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, vehicles $vehicles)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Retrieve the id_category from the request
        $idCategory = $request->input('id_vehicle');

        try {
            // Find the category by its ID
            $vehicle = vehicles::findOrFail($idCategory);

            // Delete the category
            $vehicle->delete();

            // Return success message
            return response()->json(['message' => 'Category deleted successfully!'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error deleting category: ' . $e->getMessage());

            // Return error message if the category is not found or another error occurs
            return response()->json(['error' => 'Category not found or could not be deleted.'], 404);
        }
    }

    public function getBrandsByVehicle(Request $request)    {
        $vehicleId = $request->get('vehicle_id');

        $brands = vehicles::where('id', $vehicleId)
                        ->distinct()
                        ->pluck('brand_id');

        $brandData = brands::whereIn('brand_id', $brands)->get();


        return response()->json($brandData);
    }

    public function getModelsByBrand(Request $request){
        $brandId = $request->get('brand_id');
        $category_id = $request->get("category_id");

        $models = vehicles::where('brand_id', $brandId)
                        ->where("category_id", $category_id)
                        ->pluck('model_id')
                        ->unique();

        $modelData = models::whereIn('model_id', $models)->get();

        return response()->json($modelData);
    }

    public function getGenerationsByModel(Request $request){
        $modelId = $request->get('model_id');

        $generations = vehicles::where('model_id', $modelId)
                            ->distinct()
                            ->pluck('generation_id');

        $generationData = generations::whereIn('generation_id', $generations)->get();

        return response()->json($generationData);
    }

    public function getEnginesByGeneration(Request $request){
        $generationId = $request->get('generation_id');
        $brand_id = $request->get("brand_id");
        $category_id = $request->get("category_id");
        $model_id = $request->get("model_id");

        $engines = vehicles::where('generation_id', $generationId)
                        ->where('brand_id', $brand_id)
                        ->where('category_id', $category_id)
                        ->where("model_id", $model_id)
                        ->distinct()
                        ->pluck('engine_id');

        $engineData = engines::whereIn('engine_id', $engines)->get();

        return response()->json($engineData);
    }

    public function getEcusByEngine(Request $request){
        $engineId = $request->get('engine_id');
        $generationId = $request->get('generation_id');
        $brand_id = $request->get("brand_id");
        $category_id = $request->get("category_id");
        $model_id = $request->get("model_id");

        $ecus = vehicles::where('engine_id', $engineId)
                        ->where('generation_id', $generationId)
                        ->where('brand_id', $brand_id)
                        ->where('category_id', $category_id)
                        ->where("model_id", $model_id)
                        ->distinct()
                        ->pluck('ecu_id');

        $ecuData = ecus::whereIn('ecu_id', $ecus)->get();

        return response()->json($ecuData);
    }






}
