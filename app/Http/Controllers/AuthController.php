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

        if (!$normalized['valid']) {
            return response()->json([
                'message' => $normalized['message']
            ], 422);
        }

        // create the user
        $user = User::create([
            'name' => trim($request->name),
            'phone' => $request->phone,
            // 'normalized_phone' => $normalized,
            'normalized_phone' => $normalized['formatted'],
        ]);

        return response()->json($user);
    }


}