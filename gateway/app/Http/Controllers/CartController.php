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
        
        return response()->json($response->json());
    }

    /**
     * Get user carts.
     */
    public function getUserCarts($userId)
    {
        $response = Http::get("{$this->apiBaseUrl}/carts/user/{$userId}");
        
        if ($response->successful()) {
            return response()->json($response->json());
        }
        
        return response()->json(['message' => 'User carts not found'], 404);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = Http::post("{$this->apiBaseUrl}/carts", $request->all());
        
        return response()->json($response->json(), 201);
    }

    /**
     * Display the specified cart.
     */
    public function show(string $id)
    {
        $response = Http::get("{$this->apiBaseUrl}/carts/{$id}");
        
        if ($response->successful()) {
            return response()->json($response->json());
        }
        
        return response()->json(['message' => 'Cart not found'], 404);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = Http::put("{$this->apiBaseUrl}/carts/{$id}", $request->all());
        
        if ($response->successful()) {
            return response()->json($response->json());
        }
        
        return response()->json(['message' => 'Failed to update cart'], 400);
    }

    /**
     * Remove the specified cart from the API.
     */
    public function destroy(string $id)
    {
        $response = Http::delete("{$this->apiBaseUrl}/carts/{$id}");
        
        if ($response->successful()) {
            return response()->json(null, 204);
        }
        
        return response()->json(['message' => 'Failed to delete cart'], 400);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cartResponse = Http::get("{$this->apiBaseUrl}/carts/{$cartId}");
        
        if (!$cartResponse->successful()) {
            return response()->json(['message' => 'Cart not found'], 404);
        }
        
        $cart = $cartResponse->json();
        
        $existingProducts = $cart['products'] ?? [];
        $newProducts = $request->input('products');
        
        $updatedProducts = array_merge($existingProducts, $newProducts);
        
        $updateResponse = Http::put("{$this->apiBaseUrl}/carts/{$cartId}", [
            'products' => $updatedProducts
        ]);
        
        if ($updateResponse->successful()) {
            return response()->json($updateResponse->json());
        }
        
        return response()->json(['message' => 'Failed to add products to cart'], 400);
    }
}