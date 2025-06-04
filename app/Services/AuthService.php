<?php

namespace App\Services;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthService
{
    public function login($credentials): array
    {
        $token = JWTAuth::attempt($credentials);
        
        if (!$token) {
            throw new Exception('Unauthorized', 401);
        }
        return $this->generateTokens(auth()->user());
    }

    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $user->token = JWTAuth::fromUser($user);
        return $user;
    }

    public function refresh(string $refreshToken): array
    {
        try {
            $user = JWTAuth::setToken($refreshToken)->authenticate();
            if (!$user) {
                throw new Exception('Invalid refresh token', 401);
            }
            return $this->generateTokens($user, true);
        } catch (Exception $e) {
            throw new Exception('Refresh token invalid or expired', 401);
        }
    }

    public function forgotPassword(array $data): void
    {
        $token = Str::random(6);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $data['email']],
            ['token' => bcrypt($token), 'created_at' => now()]
        );
        Mail::to($data['email'])->send(new ResetPasswordMail($token));
    }

    public function resetPassword(array $credentials): array
    {
        $status = Password::reset(
            $credentials,
            function ($user, $password) {
                $this->updateUserPassword($user, $password);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new Exception(__($status));
        }
        return ['status' => __($status)];
    }

    public function changePassword(array $data): void
    {
        $user = auth()->user();
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new Exception('Current password is incorrect', 401);
        }

        $this->updateUserPassword($user, $data['new_password']);
    }

    public function logout()
    {
        auth()->logout();
        $token = JWTAuth::getToken();
        JWTAuth::invalidate($token);
    }

    protected function generateTokens(User $user, bool $refresh = false): array
    {
        $accessToken = JWTAuth::claims([
            'token_type' => 'access',
            'exp' => Carbon::now()->addMinutes(config('jwt.ttl'))->timestamp
        ])->fromUser($user);

        $refreshToken = JWTAuth::claims([
            'token_type' => 'refresh',
            'exp' => Carbon::now()->addDays(config('jwt.refresh_ttl'))->timestamp
        ])->fromUser($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ];
    }

    protected function updateUserPassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60)
        ])->save();
    }
}

