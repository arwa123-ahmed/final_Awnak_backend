<?php
namespace App\Http\Controllers;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
   
        public function index()
    { 
        $services = Service::all();
        return response()->json($services); 
    }

        public function show($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service);
    }

    public function show_type(Request $request)
    {    
        $user = $request->user(); 
        if ($user->role === 'customer') {
        $services = Service::where('type', 'offer')->
         where('user_id', '!=', $user->id)->get();
       } 
       elseif ($user->role === 'volunteer') {
       $services = Service::where('type', 'request')->where('user_id', '!=', $user->id)->get();
       }
      return response()->json([
        'message' => 'Services retrieved successfully',
        'services' => $services
      
     ]); 

    } 

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:offer,request',
            'mode' => 'required|in:online,offline',
            'minutes' => 'required|integer|min:1',
            'timesalary'=>'required|integer'
        ]);
        $service = Service::create([
            'name' => $request->name,
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'type' => $request->type,
            'mode' => $request->mode,
            'status' => 'pending', 
            'timesalary' => $request->timesalary,
            'expires_at' => now()->addMinutes($request->minutes),

        ]);
        // role تحديد 
        return response()->json([
            'message' => 'Service created successfully, pending admin approval',
            'service' => $service
        ]);
    }
   
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        if ($request->user()->id !== $service->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:offer,request',
            'mode' => 'required|in:online,offline',
           
            'timesalary'=>'required|integer'
        ]);
        $service->update($request->all());
        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        if ($request->user()->id !== $service->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $service->delete();
        return response()->json([
            'message' => 'Service deleted successfully'
        ]);
    }

    public function showOffers(Request $request , $id)
    {
          $user = $request->user(); 
          $offers = Service::where('type', 'offer')
                               ->where('category_id', $id)
                        ->get();
             return response()->json([
               'message' => 'Requests retrieved successfully',
               'offers' => $offers
           ]);
    }

    public function showRequests(Request $request , $id)
    {
        $user = $request->user(); 
        $requests = Service::where('type', 'request')
                                  ->where('category_id', $id)
                             ->get();
           return response()->json([
             'message' => 'Requests retrieved successfully',
             'Requests' => $requests
         ]);
    }

}

