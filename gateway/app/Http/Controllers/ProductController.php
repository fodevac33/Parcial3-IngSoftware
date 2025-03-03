<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class ProductController extends Controller
{
    protected $apiBaseUrl = 'https://fakestoreapi.com';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $response = Http::get("{$this->apiBaseUrl}/products");
        $productos = $response->json();
        
        return response()->json([
            'mensaje' => 'Listado de productos obtenido exitosamente',
            'total_productos' => count($productos),
            'datos' => $productos
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $response = Http::post("{$this->apiBaseUrl}/products", $request->all());
        $productoCreado = $response->json();
        
        return response()->json([
            'mensaje' => 'Producto creado exitosamente',
            'datos' => $productoCreado
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $response = Http::get("{$this->apiBaseUrl}/products/{$id}");
        
        if ($response->successful()) {
            $producto = $response->json();
            
            return response()->json([
                'mensaje' => "Producto con ID {$id} encontrado",
                'datos' => $producto
            ]);
        }
        
        return response()->json([
            'mensaje' => "No se encontró ningún producto con el ID {$id}"
        ], 404);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $response = Http::put("{$this->apiBaseUrl}/products/{$id}", $request->all());
        
        if ($response->successful()) {
            $productoActualizado = $response->json();
            
            return response()->json([
                'mensaje' => "Producto con ID {$id} actualizado correctamente",
                'datos' => $productoActualizado
            ]);
        }
        
        return response()->json([
            'mensaje' => "Error al actualizar el producto con ID {$id}"
        ], 400);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = Http::delete("{$this->apiBaseUrl}/products/{$id}");
        
        if ($response->successful()) {
            return response()->json([
                'mensaje' => "Producto con ID {$id} eliminado correctamente"
            ], 204);
        }
        
        return response()->json([
            'mensaje' => "Error al eliminar el producto con ID {$id}"
        ], 400);
    }
}