<?php

namespace App\Services\Functional;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Resources\Model\AdminResource;
use App\Http\Resources\Model\UserResource;

class CombinedAuthService
{
    public function login(Request $request): array
    {
        $email = $request->input('email');
        $password = $request->input('password');

        // حاول على الأدمن أولاً
        $admin = Admin::where('email', $email)->first();
        if ($admin && $admin->password && Hash::check($password, $admin->password)) {
            $token = $admin->createToken('access-token')->plainTextToken;
            $admin->loadMissing(['roles','permissions']);
            return ['token' => $token, 'admin' => AdminResource::make($admin)];
        }

        // ثم حاول على اليوزر
        $user = User::where('email', $email)->first();
        if ($user && $user->password && Hash::check($password, $user->password)) {
            $token = $user->createToken('access-token')->plainTextToken;
            return ['token' => $token, 'user' => UserResource::make($user)];
        }

        throw new HttpResponseException(response()->json([
            'message' => __('messages.invalid_credentials')
        ], 422));
    }
}
