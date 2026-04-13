<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceManagementController extends Controller
{
    // كل السيرفيسز
    public function index()
    {
        $services = Service::with('user')->latest()->get();
        return response()->json($services);
    }

    // حذف سيرفيس بسبب
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $service = Service::findOrFail($id);

        $service->deleted_reason = $request->reason;
        $service->save();

        $service->delete();

        return response()->json([
            'message' => 'Service deleted with reason'
        ]);
    }
}
