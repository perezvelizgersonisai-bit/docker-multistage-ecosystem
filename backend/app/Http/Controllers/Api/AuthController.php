<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Controlador de autenticacion: registro, login, logout y perfil,
 * usando JSON Web Tokens (JWT) mediante el guard "api" (driver "jwt").
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Autenticación"},
     *     summary="Registrar un nuevo usuario",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Ana Cliente"),
     *             @OA\Property(property="email", type="string", format="email", example="ana@tienda.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuario registrado, incluye token JWT"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente.',
            'data' => [
                'user' => new UserResource($user),
                'authorization' => $this->tokenPayload($token),
            ],
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Autenticación"},
     *     summary="Iniciar sesión y obtener un token JWT",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="ana@tienda.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login exitoso, incluye token JWT"),
     *     @OA\Response(response=401, description="Credenciales incorrectas")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Las credenciales proporcionadas son incorrectas.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'data' => [
                'user' => new UserResource(Auth::guard('api')->user()),
                'authorization' => $this->tokenPayload($token),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Autenticación"},
     *     summary="Cerrar sesión e invalidar el token actual",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Sesión cerrada"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente. El token fue invalidado.',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     tags={"Autenticación"},
     *     summary="Obtener el perfil del usuario autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Perfil del usuario"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource(Auth::guard('api')->user()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function tokenPayload(string $token): array
    {
        return [
            'token' => $token,
            'type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ];
    }
}
