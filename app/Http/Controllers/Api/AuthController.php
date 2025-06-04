<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\RefreshResource;
use App\Http\Resources\RegisterResource;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        try {
            $data = $this->authService->login($data);
            return $this->successResponse($data,'Login successfully',201);
        } catch (\Exception $e) {
            return $this->messageErrorResponse($e->getMessage(),$e->getCode());
        }
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        try {
            $data = $this->authService->register($data);
            $response = RegisterResource::make($data);
            return $this->successResponse($response,'User successfully registered',201);
        } catch (\Exception $e) {
            return $this->messageErrorResponse($e->getMessage(),400);
        }
    }

    public function refresh(RefreshTokenRequest $request)
    {
        try {
        $tokenData = $this->authService->refresh($request->input('refresh_token'));
        $tokenData['token_type'] = 'bearer';
        $response = RefreshResource::make($tokenData);
        return $this->successResponse($response,'Refresh successfully',201);
        } catch (\Exception $e) {
            return $this->messageErrorResponse($e->getMessage(),401);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $data = $request->validated();
        $this->authService->forgotPassword($data);
        return $this->messageSuccessResponse('Code sent successfully',200);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $data = $request->validated();
            $status = $this->authService->resetPassword($data);
            return response()->json(['status' => $status['status']]);
        } catch (\Exception $e) {
            return $this->messageErrorResponse($e->getMessage(),400);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $data = $request->validated();
            $this->authService->changePassword($data);
            return response()->json([
                'message' => 'Password successfully changed',
            ], 200);
        } catch (\Exception $e) {
            return $this->messageErrorResponse($e->getMessage(),401);
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout();
            return $this->messageSuccessResponse('User successfully signed out',200);
        } catch (\Exception $e) {
            return $this->messageErrorResponse($e->getMessage(),500);
        }
    }
}

