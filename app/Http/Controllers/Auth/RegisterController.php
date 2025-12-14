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
        'role' => 'required|in:customer,volunteer',
        'nationality' => 'required|string',
        'country' => 'required|string',
        'city' => 'required|string',
        'street' => 'required|string',
        'phone' => 'required|string'
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'nationality' => $request->nationality,
        'country' => $request->country,
        'city' => $request->city,
        'street' => $request->street,
        'phone' => $request->phone
    ]);

    return response()->json([
        'user' => $user,
        'access_token' => $user->createToken('auth_token')->plainTextToken,
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
           'user' => $user,
           'access_token' => $user->createToken('auth_token')->plainTextToken,
           'token_type' => 'Bearer',
           'message' => 'LOGIN SUCCESSFUL'
            ]);

    }

   public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully'
    ]);
}

public function update(Request $request)
{
    $user = $request->user(); 

    $request->validate([
        'name' => 'required|string',
        'role' => 'required|in:customer,volunteer',
        'nationality' => 'required|string',
        'country' => 'required|string',
        'city' => 'required|string',
        'street' => 'required|string',
        'phone' => 'required|string'
    ]);

    $user->update($request->all());

    return response()->json([
        'message' => 'Profile updated successfully',
        'user' => $user
    ]);
}





  
}
