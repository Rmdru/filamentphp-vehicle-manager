<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function image(Vehicle $vehicle)
    {
        if (! $vehicle->image_exists) {
            return Response::make(status: 404);
        }

        $fileContents = Storage::disk('private')->get($vehicle->image_path);
        $extension = pathinfo($vehicle->image_path, PATHINFO_EXTENSION);
        
        return Response::make($fileContents, 200, [
            'Content-Type' => 'image/' . $extension,
            'Content-Disposition' => 'inline; filename="' . $vehicle->id . '.' . $extension . '"',
        ]);
    }
}
