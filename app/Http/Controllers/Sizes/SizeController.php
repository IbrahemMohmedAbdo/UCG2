<?php

namespace App\Http\Controllers\Sizes;

use App\Models\Size;
use Illuminate\Http\Request;
use App\Response\ApiResponse;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\SizeResource;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id = null)
{
    if ($id) {
        // Fetch a specific size by ID
        $size = Size::where(['appKey' => 539, 'id' => $id])->first();

        if (!$size) {
            return response()->json(['message' => 'Size not found'], 404);
        }

        return (new ApiResponse(200, __('Size details'), ['size' => new SizeResource($size)]))->send();
    } else {
        // Fetch all sizes
        $sizes = Size::where('appKey', 539)->get();

        // If $sizes is empty, return a message
        if ($sizes->isEmpty()) {
            return response()->json(['message' => 'No sizes found'], 404);
        }

        // Return a list of all sizes
        return (new ApiResponse(200, __('List of Sizes'), ['sizes' => SizeResource::collection($sizes)]))->send();
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
        //
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sizes') // Ensure the name is unique in the "sizes" table
            ],

        ]);
        $validatedData['appKey']=539;

        // Create a new size with the validated data
        $size = Size::create($validatedData);
        if($size)
        {
            return (new ApiResponse(200, __('size added successfully'), ['sizes' => new SizeResource($size)]))->send();
        }
        return (new ApiResponse(404, __('size not added'), []))->send();

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $size = Size::where('appKey',539)->find($id);
        if(!$size)
        {
            return (new ApiResponse(404, __('size not found'), []))->send();
        }

        return (new ApiResponse(200, __('Data of  Size'), ['sizes' =>  $size]))->send();
    }

    /**
     * Show the form for editing the specified resource.
     */


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $size = Size::find($id);

    if (!$size) {
        return (new ApiResponse(404, __('Size not found'), []))->send();
    }

    $request->validate([
        'name' => 'required|string|max:255|unique:sizes,name,' . $id,
    ]);

    $size->name = $request->input('name');
    $size->save();

    return (new ApiResponse(200, __('size Updated successfully'), ['sizes' => new SizeResource($size)]))->send();
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        Size::destroy($id);

        return (new ApiResponse(200, __('Size deleted'), []))->send();
    }
}
