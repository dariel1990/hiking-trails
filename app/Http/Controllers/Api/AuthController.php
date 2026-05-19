<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name')->value(),
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

        return response()->json([
            'token' => $this->issueToken($user, $request),
            'user' => $this->userPayload($user),
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

    private function issueToken(User $user, Request $request): string
    {
        $deviceName = trim((string) $request->input('device_name')) ?: 'android';

        return $user->createToken($deviceName)->plainTextToken;
    }

    /**
     * @return array{id: int, name: string, email: string}
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
