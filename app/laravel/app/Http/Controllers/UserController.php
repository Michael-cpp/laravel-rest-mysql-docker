<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    function index($email, $password)
    {
            $user= User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response([
                'message' => ['Invalid credentials']
            ], 404);
        }

        $token = $user->createToken('my-app-token')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }
}
