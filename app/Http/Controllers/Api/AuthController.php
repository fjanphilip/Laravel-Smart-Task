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

            // Revokasi/Hapus seluruh token sesi lama untuk memaksa auto-logout di perangkat lain
            $user->tokens()->delete();

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

    public function users(): JsonResponse
    {
        $users = User::all();

        return response()->json([
            'success' => true,
            'message' => 'Data users berhasil diambil',
            'data' => $users
        ], 200);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        $isSelf = $user->id === auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        if (!$isAdmin && !$isSelf) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk memperbarui user ini.'
            ], 403);
        }

        if ($isAdmin) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6',
                'role' => 'required|in:member,developer,manager,admin'
            ]);
        } else {
            // Non-admin hanya bisa update name dan password
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'password' => 'nullable|string|min:6',
            ]);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

    public function deleteUser(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menghapus akun Anda sendiri yang sedang aktif.'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.'
        ], 200);
    }
}
