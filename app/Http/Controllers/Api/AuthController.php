<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GoogleSignInRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $firstName = $request->string('first_name')->value();
        $lastName = $request->string('last_name')->value();

        $user = User::create([
            'name' => trim("{$firstName} {$lastName}"),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->string('email')->value(),
            'password' => $request->string('password')->value(),
        ]);

        return response()->json([
            'token' => $this->issueToken($user, $request),
            'user' => $this->userPayload($user),
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email')->value())->first();

        if (! $user || ! Hash::check($request->string('password')->value(), $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'This account has been deactivated'], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'token' => $this->issueToken($user, $request),
            'user' => $this->userPayload($user),
        ]);
    }

    public function googleSignIn(GoogleSignInRequest $request): JsonResponse
    {
        $client = app(GoogleClient::class);
        $client->setClientId(config('services.google.web_client_id'));
        $payload = $client->verifyIdToken($request->string('id_token')->value());

        if ($payload === false || empty($payload['email']) || ! ($payload['email_verified'] ?? false)) {
            return response()->json(['message' => 'Invalid Google token'], Response::HTTP_UNAUTHORIZED);
        }

        $existing = User::where('email', $payload['email'])->first();

        if ($existing && ! $existing->is_active) {
            return response()->json(['message' => 'This account has been deactivated'], Response::HTTP_FORBIDDEN);
        }

        $user = User::updateOrCreate(
            ['email' => $payload['email']],
            [
                'name' => $payload['name'] ?? $payload['email'],
                'google_id' => $payload['sub'],
            ]
        );

        return response()->json([
            'token' => $this->issueToken($user, $request),
            'user' => $this->userPayload($user),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'If an account exists for that email, a password reset link has been sent.',
        ]);
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->userPayload($request->user()));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->name = $request->string('name')->value();
        $user->save();

        return response()->json($this->userPayload($user));
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = $request->file('avatar')->store('avatars', 'public');
        $user->save();

        return response()->json($this->userPayload($user));
    }

    public function updatePassword(UpdatePasswordRequest $request): Response
    {
        $user = $request->user();

        if (! Hash::check($request->string('current_password')->value(), $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->password = $request->string('password')->value();
        $user->save();

        return response()->noContent();
    }

    public function deleteAccount(Request $request): Response
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json(['message' => __($status)]);
    }

    private function issueToken(User $user, Request $request): string
    {
        $deviceName = trim((string) $request->input('device_name')) ?: 'android';

        return $user->createToken($deviceName)->plainTextToken;
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
        ];
    }
}
