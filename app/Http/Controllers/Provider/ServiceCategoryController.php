<?php
namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\ServiceSubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceCategoryController extends Controller
{
    // Store a new category with its subcategory
    public function storeCategoryWithSubcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name'          => 'required|string',
            'icon'                   => 'nullable|file',
            'sub_categories'         => 'required|array|min:1',
            'sub_categories.*.name'  => 'required|string',
            'sub_categories.*.image' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // Check if category exists
        $category = ServiceCategory::where('name', $request->category_name)->first();

        // If category doesn't exist, create it
        if (! $category) {
            $icon_name = null;
            if ($request->hasFile('icon')) {
                $icon      = $request->file('icon');
                $extension = $icon->getClientOriginalExtension();
                $icon_name = time() . '.' . $extension;
                $icon->move(public_path('uploads/category_icons'), $icon_name);
            }

            // Get the authenticated provider's ID
            // $providerId = auth()->user()->id;
            $category = ServiceCategory::create([
                // 'provider_id' => $providerId,
                'name' => $request->category_name,
                'icon' => $icon_name,
            ]);
        }

        // Create subcategories
        $subcategories = [];
        foreach ($request->sub_categories as $subCategoryData) {
            $new_name = null;

            if ($request->hasFile('sub_categories.*.image')) {
                $image     = $subCategoryData['image'];
                $extension = $image->getClientOriginalExtension();
                $new_name  = time() . '.' . $extension;
                $image->move(public_path('uploads/sub_category_images'), $new_name);
            }

            // Create each subcategory
            $subcategory = ServiceSubCategory::create([
                'name'                => $subCategoryData['name'],
                'image'               => $new_name,
                'service_category_id' => $category->id,
            ]);

            $subcategories[] = $subcategory;
        }

        return response()->json([
            'status'        => true,
            'message'       => 'Category and subcategories added successfully',
            'category'      => $category,
            'subcategories' => $subcategories,
        ], 201);
    }

    // edit category with sub category
    public function UpdateCategoryWithSubcategory(Request $request, $categoryId)
    {
        $validator = Validator::make($request->all(), [
            'category_name'          => 'nullable|string',
            'icon'                   => 'nullable|file',
            'sub_categories'         => 'nullable|array',
            'sub_categories.*.id'    => 'nullable|exists:service_sub_categories,id',
            'sub_categories.*.name'  => 'nullable|string',
            'sub_categories.*.image' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // Find category or return error if not found
        $category = ServiceCategory::find($categoryId);
        if (! $category) {
            return response()->json(['status' => false, 'message' => 'Category not found.'], 404);
        }

        // Update category name if provided
        $category->name = $request->category_name ?? $category->name;

        // Handle category icon update if new icon is uploaded
        if ($request->hasFile('icon')) {
            $existingIcon = $category->icon;

            if ($existingIcon) {
                $oldIcon  = parse_url($existingIcon);
                $filePath = ltrim($oldIcon['path'], '/');
                if (file_exists($filePath)) {
                    unlink($filePath); // Delete the existing icon
                }
            }

            $icon_name = null;
            if ($request->hasFile('icon')) {
                $icon      = $request->file('icon');
                $extension = $icon->getClientOriginalExtension();
                $icon_name = time() . '.' . $extension;
                $icon->move(public_path('uploads/category_icons'), $icon_name);
            }

            $category->icon = $icon_name;
        }

        $category->save();

        // Handle subcategories (update existing ones or create new ones)
        $subcategories = [];
        if ($request->has('sub_categories')) {
            foreach ($request->sub_categories as $subCategoryData) {
                $subcategory = isset($subCategoryData['id'])
                ? ServiceSubCategory::find($subCategoryData['id'])
                : new ServiceSubCategory();

                // Skip subcategory if not found and no ID was given
                if (isset($subCategoryData['id']) && ! $subcategory) {
                    continue;
                }

                // Update subcategory
                $subcategory->name                = $subCategoryData['name'] ?? $subcategory->name;
                $subcategory->service_category_id = $category->id;

                // Handle subcategory image update if new image is uploaded
                if (isset($subCategoryData['image']) && $subCategoryData['image']) {
                    // Remove old image if exists
                    if ($subcategory->image) {
                        $oldImage  = parse_url($subcategory->image);
                        $imagePath = ltrim($oldImage['path'], '/');
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }

                    // Upload new subcategory image
                    $image     = $subCategoryData['image'];
                    $extension = $image->getClientOriginalExtension();
                    $new_name  = time() . '.' . $extension;
                    $image->move(public_path('uploads/sub_category_images'), $new_name);
                    $subcategory->image = $new_name;
                }

                $subcategory->save();
                $subcategories[] = $subcategory;
            }
        }

        return response()->json([
            'status'        => true,
            'message'       => 'Category and subcategories updated/added successfully',
            'category'      => $category,
            'subcategories' => $subcategories,
        ], 200);
    }

    // Update a subcategory and change its category if needed
    public function updateSubcategory(Request $request, $id)
    {
        try {
            $sub_category = ServiceSubCategory::with('category:id,name,icon')->findOrFail($id);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json(['status' => false, 'message' => 'Sub Category Not Found'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name'                => 'nullable|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'image'               => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $existingImage = $sub_category->image;

            // Delete old image if it exists
            if ($existingImage) {
                $relativePath = parse_url($existingImage, PHP_URL_PATH);
                $relativePath = ltrim($relativePath, '/');
                $fullPath     = public_path($relativePath);

                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            // Upload new image
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $image->move(public_path('uploads/sub_category_images'), $new_name);

            $validatedData['image'] = $new_name;
        }

        // Update the subcategory
        $sub_category->update($validatedData);

        $sub_category->refresh()->load('category:id,name,icon');

        return response()->json([
            'status'      => true,
            'message'     => 'Subcategory updated successfully',
            'subcategory' => $sub_category,
        ]);
    }

    // Delete a subcategory
    public function deleteSubcategory($id)
    {
        $sub_category = ServiceSubCategory::find($id);

        if (! $sub_category) {
            return response()->json(['status' => false, 'message' => 'SubCategory Not Found'], 401);
        }

        $sub_category->delete();

        return response()->json(['message' => 'Subcategory deleted successfully']);
    }
    //delete category
    public function deleteServiceCategory($id)
    {
        $category = ServiceCategory::find($id);

        if (! $category) {
            return response()->json(['status' => false, 'message' => 'Category Not Found'], 404);
        }

        // First, delete all associated subcategories
        ServiceSubCategory::where('service_category_id', $category->id)->delete();

        // Then, delete the category itself
        $category->delete();

        return response()->json(['status' => true, 'message' => 'Category and all its subcategories deleted successfully']);
    }

    //category list with subcategory
    public function getCategory(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->input('search');

        $category_list = ServiceCategory::with('subcategories');

        // Apply search filter if provided
        if ($search) {
            $category_list = $category_list->where('name', $search);
        }

        // Get paginated result
        $category_list = $category_list->paginate($perPage);

        if ($category_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the category list'], 401);
        }
        return response()->json(['status' => true, 'data' => $category_list], 200);
    }
    public function getSubCategory(Request $request)
    {
        $search = $request->input('search');

        $subcategory_list = ServiceSubCategory::with('category');

        if ($search) {
            $subcategory_list->where('name', 'like', "%$search%");
        }

        $subcategory_list = $subcategory_list->paginate();

        if ($subcategory_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the subcategory list'], 401);
        }

        return response()->json(['status' => true, 'data' => $subcategory_list], 200);
    }
    public function addCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        $icon_name = null;

        if ($request->hasFile('icon')) {
            $icon      = $request->file('icon');
            $icon_name = uniqid() . '.' . $icon->getClientOriginalExtension();
            $icon->move(public_path('uploads/category_icons'), $icon_name);
        }

        $category = ServiceCategory::create([
            'name' => $request->name,
            'icon' => $icon_name,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Category created successfully',
            'data'    => $category,
        ]);
    }

    public function addSubCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:service_categories,id',
            'name'        => 'required|string|max:255',
            'icon'        => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        $icon_name = null;

        if ($request->hasFile('icon')) {
            $icon      = $request->file('icon');
            $icon_name = uniqid() . '.' . $icon->getClientOriginalExtension();
            $icon->move(public_path('uploads/sub_category_images'), $icon_name);
        }

        $subcategory = ServiceSubCategory::create([
            'service_category_id' => $request->category_id,
            'name'                => $request->name,
            'image'               => $icon_name,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Subcategory created successfully',
            'data'    => $subcategory,
        ]);
    }

    public function getCategoryWiseSubcategory($id)
    {
        $subcategories = ServiceSubCategory::where('service_category_id', $id)->get();
        return response()->json([
            'status'  => true,
            'message' => 'Subcategory retreived successfully',
            'data'    => $subcategories,
        ]);
    }
}
