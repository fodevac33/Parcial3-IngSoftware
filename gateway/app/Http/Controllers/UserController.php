<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $apiBaseUrl = 'https://fakestoreapi.com';

    /**
     * Display a listing of all users.
     */
    public function index()
    {
        $response = Http::get("{$this->apiBaseUrl}/users");
        $usuarios = $response->json();
        
        return response()->json([
            'mensaje' => 'Listado de usuarios obtenido exitosamente',
            'total_usuarios' => count($usuarios),
            'datos' => $usuarios
        ]);
    }

    /**
     * Store a newly created user in the API.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'username' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // Send POST request to create a new user
        $response = Http::post("{$this->apiBaseUrl}/users", $request->all());
        $usuarioCreado = $response->json();
        
        return response()->json([
            'mensaje' => 'Usuario creado exitosamente',
            'datos' => $usuarioCreado
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        $response = Http::get("{$this->apiBaseUrl}/users/{$id}");
        
        if ($response->successful()) {
            $usuario = $response->json();
            
            return response()->json([
                'mensaje' => "Usuario con ID {$id} encontrado",
                'datos' => $usuario
            ]);
        }
        
        return response()->json([
            'mensaje' => "No se encontró ningún usuario con el ID {$id}"
        ], 404);
    }

    /**
     * Update the specified user in the API.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string',
            'email' => 'sometimes|email',
            'password' => 'sometimes|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // Send PUT request to update a user
        $response = Http::put("{$this->apiBaseUrl}/users/{$id}", $request->all());
        
        if ($response->successful()) {
            $usuarioActualizado = $response->json();
            
            return response()->json([
                'mensaje' => "Usuario con ID {$id} actualizado correctamente",
                'datos' => $usuarioActualizado
            ]);
        }
        
        return response()->json([
            'mensaje' => "Error al actualizar el usuario con ID {$id}"
        ], 400);
    }

    /**
     * Remove the specified user from the API.
     */
    public function destroy(string $id)
    {
        $response = Http::delete("{$this->apiBaseUrl}/users/{$id}");
        
        if ($response->successful()) {
            return response()->json([
                'mensaje' => "Usuario con ID {$id} eliminado correctamente"
            ], 204);
        }
        
        return response()->json([
            'mensaje' => "Error al eliminar el usuario con ID {$id}"
        ], 400);
    }

    /**
     * Authenticate user (login).
     */
    public function login(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // Send POST request for login
        $response = Http::post("{$this->apiBaseUrl}/auth/login", [
            'username' => $request->username,
            'password' => $request->password
        ]);
        
        if ($response->successful()) {
            $token = $response->json();
            
            return response()->json([
                'mensaje' => 'Inicio de sesión exitoso',
                'token' => $token
            ]);
        }
        
        return response()->json([
            'mensaje' => 'Credenciales inválidas'
        ], 401);
    }

    /**
     * Get user profile.
     */
    public function profile(Request $request)
    {
        // This would normally check the auth token
        // For FakeStoreAPI, we'll need to pass a token in the headers
        $token = $request->header('Authorization');
        
        if (!$token) {
            return response()->json([
                'mensaje' => 'Token de autenticación no proporcionado'
            ], 401);
        }

        // In a real implementation, you'd validate the token
        // For FakeStoreAPI, we'll assume it's valid and get user data
        // This is a placeholder since FakeStoreAPI doesn't have a real profile endpoint
        
        return response()->json([
            'mensaje' => 'Perfil de usuario obtenido exitosamente',
            'datos' => [
                'mensaje' => 'Esta es una simulación de perfil, ya que FakeStoreAPI no tiene este endpoint'
            ]
        ]);
    }
}