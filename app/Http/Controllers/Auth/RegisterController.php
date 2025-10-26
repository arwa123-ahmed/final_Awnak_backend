<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
            'token_type' => 'Bearer',
        ]);
       
    }

    public function login(Request $request)
    {
          $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
  //'access_token' => 'token_type' => 
        return response()->json([
             $user->createToken('auth_token')->plainTextToken,
             'message'=> 'LOGIN SUCCESSFUL',
             'USER' => $user,
             'Bearer',
        ]);
    }

    public function logout(Request $request)
{
    
   if($request->user()->currentAccessToken()->delete()){
    return response()->json([
        'message' => 'Logged out successfully'
    ]);
   } 

    return response()->json([
        'message' => 'Logged out non successfully'
    ]);
}



  
}
