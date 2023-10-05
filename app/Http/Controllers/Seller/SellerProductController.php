<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Seller $seller)
    {
        return $this->showAll($seller->products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, User $seller)
    {
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'quantity' => ['required', 'integer', 'min:1'],
            'image' => ['required', 'image']
        ]);

        $data = $request->all();
        $data['status'] = Product::PRODUCTO_NO_DISPONIBLE;
        $data['image'] = $this->saveImage($request);
        $data['seller_id'] = $seller->id;
        $product = Product::create($data);

        return $this->showOne($product, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $this->validate($request, [
            'quantity' => ['integer', 'min:1'],
            'status' => 'in:' . Product::PRODUCTO_DISPONIBLE . ',' . Product::PRODUCTO_NO_DISPONIBLE,
            'image' => 'image'
        ]);

        $this->verificarVendedor($seller, $product);

        $product->fill($request->only(['name', 'description', 'quantity']));

        if ($request->has('status')) {
            $product->status = $request->status;

            if ($product->estaDisponible() && $product->categories()->count() == 0) {
                return $this->errorResponse('Un producto activo debe tener al menos una categoria', 409);
            }
        }

        if ($request->hasFile('image')) {
            Storage::disk('images')->delete($product->image);
            $product->image = $this->saveImage($request);
        }

        if ($product->isClean()) {
            return $this->errorResponse('Se debe especificar al menos un valor diferente para actualizar', 422);
        }

        $product->save();

        return $this->showOne($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Seller $seller, Product $product)
    {
        $this->verificarVendedor($seller, $product);
        Storage::disk('images')->delete($product->image);
        $product->delete();
        return $this->showOne($product);
    }

    protected function verificarVendedor(Seller $seller, Product $product)
    {
        if ($seller->id != $product->seller_id) {
            throw new HttpException(422, 'El vendedor especificado no es el vendedor real del producto');
        }
    }

    private function saveImage($request): string
    {
        /*------------------------------------------------------*/
        // Get Image
        $file = $request->file('image');
        // Set a unique name for the image
        $filename = time() . $file->getClientOriginalName();
        // Save in disk
        Storage::disk('images')->put($filename, File::get($file));
        /*------------------------------------------------------*/

        return $filename;
    }
}
