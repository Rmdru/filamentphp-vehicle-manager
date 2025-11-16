<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function image(Vehicle $vehicle)
    {
        $imageBasePath = 'vehicles/' . $vehicle->id;

        $extensions = ['jpg', 'jpeg', 'png', 'gif'];
        foreach ($extensions as $extension) {
            $imagePath = $imageBasePath . '.' . $extension;
    
            if (Request::secure()) {
                if (Storage::disk('private')->exists($imagePath)) {
                    $fileContents = Storage::disk('private')->get($imagePath);
                    return Response::make($fileContents, 200, [
                        'Content-Type' => 'image/' . $extension,
                        'Content-Disposition' => 'inline; filename="' . $vehicle->id . '.' . $extension . '"',
                    ]);
                }
            }
            
            if (Storage::disk('public')->exists($imagePath)) {
                return response()->file(Storage::disk('public')->path($imagePath));
            }
        }

        return abort(404);
    }
}
