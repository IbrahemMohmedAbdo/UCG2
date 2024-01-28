<?php

namespace App\Http\Controllers\Categories;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Response\ApiResponse;
use App\Services\MediaService;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\addCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{

    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function getCategories()
    {
		$user = Auth::user();
		
				$permissions = $user->getPermissionsViaRoles();
		 $permissionNames = $permissions->pluck('name')->toArray();
		$desiredPermission = 'category-list539';
		
		if (!in_array($desiredPermission, $permissionNames)) {
        return (new ApiResponse(403, __('Unauthorized: You don\'t have permission to see this page'), []))->send();
    }
		
        $categories = Category::where('appKey',539)
            ->where('category_id', '=', null)
            ->get();

        if (!$categories) {
            return (new ApiResponse(404, __('not found'), []))->send();
        }
        return (new ApiResponse(200, __('list of categories'), ['category' => CategoryResource::collection($categories)]))->send();
    }
    public function addCategory(addCategoryRequest $request)
    {
		  $user = Auth::user();
	
					$permissions = $user->getPermissionsViaRoles();
		 $permissionNames = $permissions->pluck('name')->toArray();
		$desiredPermission = 'category-create539';
		
		if (!in_array($desiredPermission, $permissionNames)) {
        return (new ApiResponse(403, __('Unauthorized: You don\'t have permission to see this page'), []))->send();
    }
        $validatedData = $request->validated();

        $validatedData['appKey'] = 539;
			
        $category = Category::create($validatedData);

        return (new ApiResponse(200, __('category added successfully'), ['category' => new  CategoryResource($category)]))->send();
    }
	
	 public function updateCategory( UpdateCategoryRequest $request ,$id)
    {
        $validatedData = $request->validated();
        $Category = Category::findOrFail($id);
        if (!$Category) {
            return (new ApiResponse(404, __('product not found'), []))->send();
        }
        $Category->update($validatedData);

        return (new ApiResponse(200, __('product updated successfully'), ['product' => new  CategoryResource($Category)]))->send();


    }




    public function deleteCategory($id)
    {

        $category=Category::find($id);
        // Check if the category exists
    if (!$category) {
        return (new ApiResponse(404, __('Category not found.'), []))->send();
    }

    // Delete all associated products
    $category->products()->delete();

    // Delete the category
    if ($category->delete()) {
        return (new ApiResponse(200, __('Category deleted successfully'), []))->send();
    } else {
        return (new ApiResponse(500, __('Failed to delete category'), []))->send();
    }
	}
	
}
