<?php

namespace App\Http\Controllers\Api;

use App\Models\Seo;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::all();

        return response()->json([
            'status' => 'success',
            'count' => count($posts),
            'data' => $posts
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'category_id' => 'required|numeric',
            'title' => 'required',
            'content' => 'required',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        // Check if user is same as loggedin user
        $loggedInUser = Auth::user();

        if($loggedInUser->id != $request->user_id){
            return response()->json([
                'status' => 'fail',
                'message' => 'Un-authorized access'
            ], 400);
        }

        // check if category id is exits in DB
        $category = Category::find($request->category_id);
        if(!$category){
            return response()->json([
                'status' => 'fail',
                'message' => 'Category not found',
            ], 404);
        }

        $imagePath = null;
        if($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()){
            $file = $request->file('thumbnail');

            // Generate unique file name
            $fileName = time().'_'.$file->getClientOriginalName();

            //Move file into storage
            $file->move(public_path('storage/posts'), $fileName);

            //Save image path into our database
            $imagePath = "storage/posts/".$fileName;
        }

        $data['title'] = $request->title;
        $data['slug'] = Str::slug($request->title);
        $data['user_id'] = $request->user_id;
        $data['category_id'] = $request->category_id;
        $data['content'] = $request->content;
        $data['excerpt'] = $request->excerpt;
        $data['thumbnail'] = $imagePath ? $imagePath : null;
        if(Auth::user()->role == 'admin'){
            $data['status'] = 'published';
        }
        if(Auth::user()->role == 'admin' || Auth::user()->role == 'author')
        $data['published_at'] = date('Y-m-d H:i:s');

        $blogPost = Post::create($data); // It will new blog posts

    

        return response()->json([
            'status' => 'success',
            'message' => 'Blog post created successfully'
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

     public function update(Request $request, int $id)
    {
        // Check Blog Post
        $blogPost = Post::find($id);
        if(!$blogPost){
            return response()->json([
                'status' => 'fail',
                'message' => 'No Blog Post Found'
            ], 404);
        }


        // Validate Input
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'category_id' => 'required|numeric',
            'title' => 'required',
            'content' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        // Check if user is same as loggedin user
        $loggedInUser = Auth::user();

          if($loggedInUser->id != $request->user_id){
            return response()->json([
                'status' => 'fail',
                'message' => 'Un-authorized access'
            ], 400);
        }

     

        // check if category id is exits in DB
        $category = Category::find($request->category_id);
        if(!$category){
            return response()->json([
                'status' => 'fail',
                'message' => 'Category not found',
            ], 404);
        }

        // Check additional condition to restrict authorized edit
        if($loggedInUser->id == $blogPost->user_id || Auth::user()->role == 'admin'){
            $blogPost->category_id = $request->category_id;
            $blogPost->user_id = $request->user_id;
            $blogPost->title = $request->title;
            $blogPost->slug = Str::slug($request->title);
            $blogPost->content = $request->content;
            $blogPost->excerpt = $request->excerpt;
            $blogPost->status = $request->status;
            $blogPost->save(); // IT will update the record in databas


            return response()->json([
                'status' => 'success',
                'message' => 'Blog Post editted successfully!'
                ], 201);
        }else{
            return response()->json([
                'status' => 'fail',
                'message' => 'You are not allowed to perform this task'
                ], 403);
        }

    

    }

   public function blogPostImage(Request $request, int $id)
   {
        // Check Blog Post
        $blogPost = Post::find($id);
        if(!$blogPost){
            return response()->json([
                'status' => 'fail',
                'message' => 'No Blog Post Found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        // Check if user is same as logged in user
        $loggedInUser = Auth::user();

        if($loggedInUser->id != $request->user_id){
            return response()->json([
                'status' => 'fail',
                'message' => 'Un-authorized access'
            ], 400);
        }

        if($loggedInUser->id == $blogPost->user_id || Auth::user()->role == 'admin'){

            // Image Upload
            $imagePath = null;

            if($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()){

                // ==== DELETE OLD IMAGE ====
                if($blogPost->thumbnail && File::exists(public_path($blogPost->thumbnail))){
                    File::delete(public_path($blogPost->thumbnail));
                }

                // Upload new image
                $file = $request->file('thumbnail');
                $fileName = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('storage/posts'), $fileName);
                $imagePath = "storage/posts/".$fileName;
            }

            // Update DB
            $blogPost->thumbnail = $imagePath ?? $blogPost->thumbnail;
            $blogPost->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Blog image updated successfully'
            ], 201);

        }else{
            return response()->json([
                'status' => 'fail',
                'message' => 'You are not allowed to perform this task'
            ], 403);
        }
    }

   

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        // Check blog post
        $blogPost = Post::find($id);
        if(!$blogPost){
            return response()->json([
                'status' => 'fail',
                'message' => 'No Blog Post Found'
            ], 404);
        }

        // Check if user is same as logged in user
        $loggedInUser = Auth::user();
        if($loggedInUser->id == $blogPost->user_id || Auth::user()->role == 'admin'){

            // ==== DELETE OLD IMAGE ====
            if($blogPost->thumbnail && File::exists(public_path($blogPost->thumbnail))){
                File::delete(public_path($blogPost->thumbnail));
            }

            // Delete post
            $blogPost->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Post deleted successfully'
            ], 201);

        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'You are not allowed to perform this task'
            ], 403);
        }
    }
 
}