<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;


class AuthController extends Controller
{

    public function login(AuthLoginRequest $request): JsonResponse
    {

        $credentials = $request->only("email", "password");

        if (Auth::attempt($credentials)) {

            $user = Auth::user();


            $token = $user->createToken('auth_token')->plainTextToken;

            $user->remember_token = $token;

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil, silakan simpan token Anda.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah.',
            'access_token' => null,
            'token_type' => null,
            'user' => null
        ], 401);
    }

    public function register(AuthRegisterRequest $request): JsonResponse
    {

        $validateData = $request->validated();

        $user = User::create($validateData);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil, silakan login.',
            'access_token' => $token,
            'token_type' => 'Bearer Token',
            'user' => $user
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->user()->currentAccessToken()->delete();
        $user->remember_token = null;
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil, token telah dihapus.'
        ], 200);
    }


    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'data' => $user
        ], 200);
    }
}
