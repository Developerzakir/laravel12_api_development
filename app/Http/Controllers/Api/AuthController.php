<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
          $validator = Validator::make($request->all(),[
            'name'=>'required|min:4',
            'email'=>'required|unique:users,email',
            'password'=>'required|min:8|confirmed',
            'password_confirmation'=>'required|min:8'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>'fail',
                'message'=>$validator->errors()
            ],400);
        }

        $data = $request->all();

        //image upload
        $imagePath = null;

        if($request->hasFile('profile_picture') && $request->file('profile_picture')->isValid()){
            $file = $request->file('profile_picture');

            //generate unique filename
            $fileName = time().'-'.$file->getClientOriginalName();

            //move file to the public directory
            $file->move(public_path('storage/profile'),$fileName);

            //save the related path to the database
            $imagePath = "storage/profile/".$fileName;
        }
        $data['profile_picture'] = $imagePath;

        User::create($data); 

         return response()->json([
            'status'=>'success',
            'message'=>'New user created successfully!'
        ],201);
    }

    public function login(Request $request)
    {
         $validator = Validator::make($request->all(),[
            'email'=>'required',
            'password'=>'required'
        ]);

          if($validator->fails()){
                return response()->json([
                    'status'=>'fail',
                    'message'=>$validator->errors()
                ],400);
            }

            if(Auth::attempt(['email'=>$request->email, 'password'=>$request->password])){

                $user = Auth::user();

                $response['token']= $user->createToken('Blogapp')->plainTextToken;
                $response['email'] = $user->email;
                $response['name'] = $user->name;

                  return response()->json([
                        'status'=>'success',
                        'message'=>'login successfully!',
                        'data'=>$response
                    ],200);
                
            }else{
                 return response()->json([
                    'status'=>'fail',
                    'message'=>'invalid credential'
                ],400); 
            }
    }

    public function profile()
    {
        $user = Auth::user();

         return response()->json([
            'status'=>'success',
            'data'=>$user
        ]);
    }

    public function logout()
    {
          $user = Auth::user();
          $user->tokens()->delete();

           return response()->json([
            'status'=>'success',
            'message'=>'user logout'
        ],200);

    }
}
