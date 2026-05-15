<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    // Admin melihat SEMUA alat, termasuk yang sedang maintenance
    public function index(): JsonResponse
    {
        $equipments = Equipment::latest()->get();
        return response()->json(['success' => true, 'data' => $equipments]);
    }

    // Admin menambahkan alat/ruangan baru
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:workspace,studio_gear',
            'stock' => 'required|integer|min:1',
            'status' => 'required|in:available,maintenance,in_use',
            'sku' => 'nullable|string|max:100|unique:equipments,sku',
            'location' => 'nullable|string|max:255',
            'condition_notes' => 'nullable|string'
        ]);
        $validated['sku'] = $validated['sku'] ?? 'SKU-' . strtoupper(uniqid());
        $equipment = Equipment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventaris berhasil ditambahkan',
            'data' => $equipment
        ], 201);
    }

    // Admin mengupdate data alat
    public function update(Request $request, string $id): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:workspace,studio_gear',
            'stock' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:available,maintenance,in_use,retired'
        ]);

        $equipment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventaris berhasil diperbarui',
            'data' => $equipment
        ]);
    }

    // Admin menghapus alat
    public function destroy(string $id): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventaris berhasil dihapus'
        ]);
    }
}
