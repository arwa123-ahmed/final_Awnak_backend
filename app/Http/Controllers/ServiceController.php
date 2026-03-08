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

    // يعمل سيرفس
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:offer,request',
            'service_location'=>'nullable|string|max:255',
            'timesalary'=>'required|integer'
        ]);
          $user = $request->user();
        if ($user->role == 'customer' && $request->type == 'offer')
             {
             return response()->json([
            'message' => 'Customer can only create requests'], 403);
             }

        if ($user->role == 'volunteer' && $request->type == 'request')
        {
        return response()->json([
        'message' => 'Volunteer can only create offers'], 403);
        }
        $service = Service::create([
        'name' => $request->name,
        'description' => $request->description,
        'service_location' => $request->service_location,
        'user_id' => $user->id,
        'category_id' => $request->category_id,
        'type' => $request->type,
        'status' => 'pending',
        'timesalary' => $request->timesalary,
        ]);
       
        return response()->json([
            'message' => 'Service created successfully',
            'service' => $service
        ]);
    }
   
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        if ($request->user()->id !== $service->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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

    //يعرض كل الاوفر بكتايجوري معينه ولسا ماتاخدت
         public function showOffers(Request $request, $id)
         {
             $user = $request->user(); 
             $offers = Service::with('user')
                              ->where('type', 'offer')
                              ->where('category_id', $id)
                              ->orderByRaw("status = 'pending' DESC")
                              ->get();
         
             return response()->json([
                 'message' => 'Requests retrieved successfully',
                 'offers' => $offers
             ]);
         }

    //يعرض كل الريكوست بكتايجوري معينه ولسا ماتاخدت
    public function showRequests(Request $request, $id)
    {
            $user = $request->user(); 
            $requests = Service::with('user')
                           ->where('type', 'request')
                           ->where('category_id', $id)
                           ->orderByRaw("status = 'pending' DESC")
                           ->get();
        return response()->json([
            'message' => 'Requests retrieved successfully',
            'Requests' => $requests
        ]);
    }

    //يعرض الاوفر الخاصه باليوزر بسس
    public function myOffers(Request $request)
    {
          $user = $request->user();
          $offers = Service::where('user_id', $user->id)
                     ->where('type', 'offer')
                     ->get();
 
           return response()->json([
               'message' => 'Your offers',
               'offers' => $offers
           ]);
    }

    //يعرض ريكوست الخاصه باليوزر بسس
    public function myRequests(Request $request)
    {
        $user = $request->user();
        $requests = Service::where('user_id', $user->id)
                       ->where('type', 'request')
                       ->get();
        return response()->json([
            'message' => 'Your requests',
            'requests' => $requests
        ]);
    }

}

