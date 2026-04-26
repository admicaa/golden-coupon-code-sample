<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\AdminLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!Auth::guard('admin-web')->attempt($credentials, true)) {
            return new JsonResponse(['message' => trans('auth.failed')], 401);
        }

        $admin = Auth::guard('admin-web')->user();
        $token = $admin->createToken('Admin Token');

        return new JsonResponse([
            'user' => $admin,
            'token' => $token->accessToken,
        ]);
    }
}
