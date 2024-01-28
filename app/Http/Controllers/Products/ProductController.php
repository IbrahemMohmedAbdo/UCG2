<?php

namespace App\Http\Controllers\Products;

use App\Models\Product;
use App\Models\Variation;
use Illuminate\Http\Request;
use App\Response\ApiResponse;
use App\Services\FileService;
use App\Services\MediaService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\addProductRequest;
use App\Http\Resources\GetProductResource;
use App\Http\Requests\updateProductRequest;

class ProductController extends Controller
{

   protected $mediaService;
    protected $fileService;
    public function __construct(MediaService $mediaService,FileService $fileService)
    {
        $this->mediaService = $mediaService;
       // $this->fileService = $fileService;

    }





    //
public function getProducts()
{
    // Get the currently authenticated user
    $user = auth()->user();

    // Check if the user is a store manager
    if (!$user->type =="driver" ) {
        // Retrieve products created by the store manager
        $products = Product::where('appKey', 539)->latest()
            ->get();

        if ($products->isEmpty()) {
            return (new ApiResponse(200, __('There is No product for this user'), ['product'=>[]]))->send();
        }

        return (new ApiResponse(200, __('List of products'), ['product' => GetProductResource::collection($products)]))->send();
    }
	else{
		 $products = Product::where('appKey', 539)
            ->latest()
            ->get();
		 return (new ApiResponse(200, __('List of products'), ['product' => GetProductResource::collection($products)]))->send();
	}

    // If the user is not a store manager, return an error response
    return (new ApiResponse(403, __('You do not have permission to access this resource'), []))->send();
}


    public function getProductById($id)
    {



        $product = Product::find($id);

        if (!$product) {
            return (new ApiResponse(200, __('product not found'), ['product'=>[]]))->send();
        }
        return (new ApiResponse(200, __('product by id'), ['product' => new  ProductResource($product)]))->send();
    }
    public function addProduct(addProductRequest $request)
    {
        $user = auth()->user();
        $validatedData = $request->validated();
        $validatedData['appKey'] = 539;
        $validatedData['user_id'] = $request->user_id ?? $user->id;

        $product = $this->createProduct($validatedData, $user);

        DB::transaction(function () use ($product, $validatedData, $request) {
            $this->addVariationsAndMedia($product, $validatedData, $request);
        });

        if (!$product) {
            return (new ApiResponse(404, __('product not added'), []))->send();
        }

        return (new ApiResponse(200, __('product added successfully'), ['product' => new ProductResource($product)]))->send();
    }

    protected function createProduct($validatedData, $user)
    {
        return $user->type === 'adminCG'
            ? Product::create($validatedData)
            : $user->products()->create($validatedData);
    }

    protected function addVariationsAndMedia($product, $validatedData, $request)
    {
        foreach ($validatedData['colors'] as $colorData) {
            $colorId = $colorData['colorId'];
            $sizeId = $colorData['sizeId'];
            $quantity = $colorData['quantity'];
            $images = $colorData['images'];
            $imagePath = $this->uploadImage($images);

            $this->createVariation($product, $colorId, $sizeId, $quantity, $imagePath);
        }

        $video = $request->file('videos');
        if ($video) {
            $this->mediaService->updateOrCreateMedia($product, $video);
        }
    }

    protected function createVariation($product, $colorId, $sizeId, $quantity, $imagePath)
    {
        $product->variations()->create([
            'color_id' => $colorId,
            'size_id' => $sizeId,
            'quantity' => $quantity,
            'image_path' => $imagePath,
        ]);
    }
private function uploadImage($image)
{
    // Ensure that an image was provided
    if (!$image instanceof \Illuminate\Http\UploadedFile) {
        return null;
    }

    // Generate a unique filename for the image
    $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();

    // Get the full path to the public directory
    $publicPath = public_path();

    // Move the image to the public directory
    $image->move($publicPath, $filename);

    // Get the URL path to the stored image
    $urlPath = url($filename);

    // Return the URL path to the stored image
    return $urlPath;
}









public function updateProduct(updateProductRequest $request, $id)
{
    $user = auth()->user();
    $validatedData = $request->validated();
	//dd($validatedData );
    $product = Product::find($id);

    if (!$product) {
        return (new ApiResponse(200, __('product not found'), ['product' => []]))->send();
    }

    $validatedData['user_id'] = $request->user_id ?? $user->id;

    $this->addOrUpdateVariationsAndMedia($product, $validatedData, $request);
    $product->update($validatedData);
    $product->load('media');
    // Refresh the model to get the updated attributes from the database
    $product->refresh();

    return (new ApiResponse(200, __('product updated successfully'), ['product' => new ProductResource($product)]))->send();
}

protected function addOrUpdateVariationsAndMedia($product, $validatedData, $request)
{
	 $video=$request->file('videos');
    if($request->file('videos')){ $this->mediaService->updateOrCreateMedia($product, $video);}
    $this->updateProductVariations($product, $validatedData);


}
protected function updateProductVariations($product, $validatedData)
{
    if (array_key_exists('colors', $validatedData)) {
        foreach ($validatedData['colors'] as $colorData) {
            $colorId = $colorData['colorId'];
            $sizeId = $colorData['sizeId'];
            $quantity = $colorData['quantity'];
            $image = $colorData['images'];

            // Upload the image and get the path
            $imagePath = $this->updateImage($image);

            // Find or create the variation based on colorId and sizeId
            $variation = $product->variations()->firstOrNew([
                'color_id' => $colorId,
                'size_id' => $sizeId,
            ]);

            // Update the variation attributes
            $variation->quantity = $quantity;
            $variation->image_path = $imagePath;

            // Save the variation
            $variation->save();
        }
    } 
}


private function updateImage($image, $existingFilename = null, $removeExisting = false)
{
    // Remove existing image if requested
    if ($removeExisting && $existingFilename) {
        $this->removeImage($existingFilename);
    }

    // Ensure that an image was provided
    if (!$image instanceof \Illuminate\Http\UploadedFile) {
        return null;
    }

    // Generate a unique filename for the image if not provided
    $filename = $existingFilename ?? uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();

    // Get the full path to the public directory
    $publicPath = public_path();

    // Move the image to the public directory
    $image->move($publicPath, $filename);

    // Get the URL path to the stored image
      $urlPath = url($filename);

    // Return the URL path to the stored image
    return $urlPath;
}





	public function deleteProduct($id)
    {
        $product=Product::find($id);
        // Check if the category exists
    if (!$product) {
        return (new ApiResponse(404, __('product not found.'), []))->send();
    }



    // Delete the Product
    if ($product->delete()) {
        return (new ApiResponse(200, __('product deleted successfully'), []))->send();
    } else {
        return (new ApiResponse(500, __('Failed to delete product'), []))->send();
    }
    }
}
