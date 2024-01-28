<?php

namespace App\Http\Controllers\Colors;

use App\Models\Color;
use Illuminate\Http\Request;
use App\Response\ApiResponse;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\ColorResource;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id = null)
    {
        if ($id) {
            // Fetch a specific color by ID
            $color = Color::where(['appKey' => 539, 'id' => $id])->first();

            if (!$color) {
                return response()->json(['message' => 'Color not found'], 404);
            }

            return (new ApiResponse(200, __('Color details'), ['color' => new ColorResource($color)]))->send();
        } else {
            // Fetch all colors
            $colors = Color::where('appKey', 539)->get();

            // If $colors is empty, return a message
            if ($colors->isEmpty()) {
                return response()->json(['message' => 'No colors found'], 404);
            }

            // Return a list of all colors
            return (new ApiResponse(200, __('List of Colors'), ['colors' => ColorResource::collection($colors)]))->send();
        }
    }

    /**
     * Show the form for creating a new resource.
     */


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('colors'), // Ensure the name is unique in the "colors" table
            ],
            'value' => 'string|max:255', // Add this line
        ]);

        $validatedData['appKey'] = 539;

        // Create a new color with the validated data
        $color = Color::create($validatedData);

        if ($color) {
            return (new ApiResponse(200, __('Color added successfully'), ['color' => new ColorResource($color)]))->send();
        }

        return (new ApiResponse(404, __('Color not added'), []))->send();
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $color = Color::find($id);

        if (!$color) {
            return (new ApiResponse(404, __('Color not found'), []))->send();
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:colors,name,' . $id,
            'value' => 'string|max:255', // Add this line
        ]);

        $color->name = $request->input('name');
        $color->value = $request->input('value'); // Add this line
        $color->save();

        return (new ApiResponse(200, __('Color Updated successfully'), ['color' => new ColorResource($color)]))->send();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Color::destroy($id);

        return (new ApiResponse(200, __('Color deleted'), []))->send();
    }
}
