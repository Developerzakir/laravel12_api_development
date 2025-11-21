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
}
