<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\AdminLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate an admin and issue a Passport personal access token.
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        $provider = Auth::createUserProvider('admins');
        $admin = $provider?->retrieveByCredentials($credentials);

        if (!$admin || !$provider->validateCredentials($admin, $credentials)) {
            return new JsonResponse(['message' => trans('auth.failed')], 401);
        }

        $token = $admin->createToken('Admin Token');

        return new JsonResponse([
            'user' => $admin,
            'token' => $token->accessToken,
        ]);
    }
}
