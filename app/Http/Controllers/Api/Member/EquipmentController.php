<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(): JsonResponse
    {
        $equipments = Equipment::where('status', 'available')
            ->where('stock', '>', 0)
            ->get();

        return response()->json(['success' => true, 'data' => $equipments]);
    }
}
