<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeviceRequest;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function create(DeviceRequest $request):JsonResponse
    {
        Device::query()->create([
            'name' => $request->name,
            'building_id' => $request->building_id,
            'type' => $request->type
        ]);
        return response()->json(['success' => true]);
    }
}
