<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    protected $apiBaseUrl = 'https://fakestoreapi.com';

    /**
     * Display a listing of all carts.
     */
    public function index()
    {
        $response = Http::get("{$this->apiBaseUrl}/carts");
        $carritos = $response->json();
        
        return response()->json([
            'mensaje' => 'Listado de carritos obtenido exitosamente',
            'total_carritos' => count($carritos),
            'datos' => $carritos
        ]);
    }

    /**
     * Get user carts.
     */
    public function getUserCarts($userId)
    {
        $response = Http::get("{$this->apiBaseUrl}/carts/user/{$userId}");
        
        if ($response->successful()) {
            $carritosUsuario = $response->json();
            
            return response()->json([
                'mensaje' => "Carritos del usuario con ID {$userId} obtenidos exitosamente",
                'total_carritos' => count($carritosUsuario),
                'datos' => $carritosUsuario
            ]);
        }
        
        return response()->json([
            'mensaje' => "No se encontraron carritos para el usuario con ID {$userId}"
        ], 404);
    }

    /**
     * Store a newly created cart in the API.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'userId' => 'required|integer',
            'products' => 'required|array',
            'products.*.productId' => 'required|integer',
            'products.*.quantity' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // Send POST request to create a new cart
        $response = Http::post("{$this->apiBaseUrl}/carts", $request->all());
        $carritoCreado = $response->json();
        
        // Return JSON response with the created cart
        return response()->json([
            'mensaje' => 'Carrito creado exitosamente',
            'datos' => $carritoCreado
        ], 201);
    }

    /**
     * Display the specified cart.
     */
    public function show(string $id)
    {
        $response = Http::get("{$this->apiBaseUrl}/carts/{$id}");
        
        if ($response->successful()) {
            $carrito = $response->json();
            
            return response()->json([
                'mensaje' => "Carrito con ID {$id} encontrado",
                'datos' => $carrito
            ]);
        }
        
        return response()->json([
            'mensaje' => "No se encontró ningún carrito con el ID {$id}"
        ], 404);
    }

    /**
     * Update the specified cart in the API.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'userId' => 'sometimes|integer',
            'products' => 'sometimes|array',
            'products.*.productId' => 'required_with:products|integer',
            'products.*.quantity' => 'required_with:products|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // Send PUT request to update a cart
        $response = Http::put("{$this->apiBaseUrl}/carts/{$id}", $request->all());
        
        if ($response->successful()) {
            $carritoActualizado = $response->json();
            
            return response()->json([
                'mensaje' => "Carrito con ID {$id} actualizado correctamente",
                'datos' => $carritoActualizado
            ]);
        }
        
        return response()->json([
            'mensaje' => "Error al actualizar el carrito con ID {$id}"
        ], 400);
    }

    /**
     * Remove the specified cart from the API.
     */
    public function destroy(string $id)
    {
        $response = Http::delete("{$this->apiBaseUrl}/carts/{$id}");
        
        if ($response->successful()) {
            return response()->json([
                'mensaje' => "Carrito con ID {$id} eliminado correctamente"
            ], 204);
        }
        
        return response()->json([
            'mensaje' => "Error al eliminar el carrito con ID {$id}"
        ], 400);
    }

    /**
     * Add products to a cart.
     */
    public function addProducts(Request $request, string $cartId)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.productId' => 'required|integer',
            'products.*.quantity' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        // First get the current cart
        $cartResponse = Http::get("{$this->apiBaseUrl}/carts/{$cartId}");
        
        if (!$cartResponse->successful()) {
            return response()->json([
                'mensaje' => "No se encontró ningún carrito con el ID {$cartId}"
            ], 404);
        }
        
        $cart = $cartResponse->json();
        
        // Add new products to existing ones
        $existingProducts = $cart['products'] ?? [];
        $newProducts = $request->input('products');
        
        // Simple merge - in a real app you might want to aggregate quantities for the same product
        $updatedProducts = array_merge($existingProducts, $newProducts);
        
        // Update the cart
        $updateResponse = Http::put("{$this->apiBaseUrl}/carts/{$cartId}", [
            'products' => $updatedProducts
        ]);
        
        if ($updateResponse->successful()) {
            $carritoActualizado = $updateResponse->json();
            
            return response()->json([
                'mensaje' => "Productos agregados al carrito con ID {$cartId} correctamente",
                'datos' => $carritoActualizado
            ]);
        }
        
        return response()->json([
            'mensaje' => "Error al agregar productos al carrito con ID {$cartId}"
        ], 400);
    }
}