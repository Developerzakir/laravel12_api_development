<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $categories = Category::get();

        return response()->json([
            'status' => 'success',
            'count' => count($categories),
            'data' => $categories
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        $data['name'] = $request->name;
        $data['slug'] = Str::slug($request->name);

        Category::create($data); // Create new record in databse table

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        $category = Category::find($id);

        if($category){

            $category->name = $request->name;
            $category->slug = Str::slug($request->name);
            $category->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Category editted successfully'
            ], 201);

        } else{
            return response()->json([
                'status' => 'fail',
                'message' => 'Category Not Found'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);

        if($category){

            Category::destroy($id); // It will remove the category from our DB

            return response()->json([
                'status' => 'success',
                'message' => 'Category Deleted Successfully'
            ], 201);
        } else{
            return response()->json([
                'status' => 'fail',
                'message' => 'Category Not Found'
            ], 404);
        }
    }
}