<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\PhoneService;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // validation to the request
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone'
        ]);

        // normalize phone
        $normalized = PhoneService::normalize($request->phone);

        // create the user
        $user = User::create([
            'name' => trim($request->name),
            'phone' => $request->phone,
            'normalized_phone' => $normalized,
        ]);

        return response()->json($user);
    }


}