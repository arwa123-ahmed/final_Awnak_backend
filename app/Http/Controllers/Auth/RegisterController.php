<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\OtpMail;
use App\Mail\EmailCheck;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
/*
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
*/


    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        $otp = rand(1000, 9999);
        Cache::put('otp_' . $request->email, $otp, now()->addMinutes(10));
        Mail::to($request->email)->send(new OtpMail($otp));
        return response()->json(['message' => 'OTP sent to email successfully']);
    }

    // VERIFY OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);
        $cachedOtp = Cache::get('otp_' . $request->email);
        if (!$cachedOtp) {
            return response()->json(['message' => 'OTP expired'], 422);
        }
        if ($cachedOtp != $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 422);
        }
        //check about email
        Cache::put('verified_' . $request->email, true, now()->addMinutes(10));
        return response()->json([
            'message' => 'OTP verified successfully'
        ]);
    }

    // REGISTERATION
    public function register(Request $request)
    {
        $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'role' => 'required|in:customer,volunteer',
        'nationality' => 'required|string',
        'country' => 'required|string',
        'city' => 'required|string',
        'street' => 'required|string',
        'phone' => 'required|string',
        'password' => 'required|min:6',
        'gender' => 'required|in:male,female',
        'id_image' => 'nullable|file'
        ]);
        // check about email status
       if (!Cache::get('verified_' . $request->email)) {
           return response()->json(['message' => 'Email not verified'], 422);
        }
        //default img if img not exist
        $path = 'ids/user.png';
       if ($request->hasFile('id_image')) {
            //uplod card id image
           $path = $request->file('id_image')->store('ids', 'public');
        }
        // create user
        $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'role' => $request->role,
        'nationality' => $request->nationality,
        'country' => $request->country,
        'city' => $request->city,
        'street' => $request->street,
        'phone' => $request->phone, 
        'password' => Hash::make($request->password),
        'gender' => $request->gender,
        'id_image' => $path
        ]);
        // delete otp
       Cache::forget('otp_' . $request->email);
        Cache::forget('verified_' . $request->email);

        return response()->json([
            'message' => 'User Registered Successfully!',
            'user' => $user
        ]);
    }

    //check email
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        // check if the email exist
        $exists = User::where('email', $request->email)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $credintial = $request->only('email', 'password');
        if (!Auth::attempt($credintial)) {
            return response()->json(['message' => "invalid credintial"]);
        }
        $user = Auth::user(); //store user data
       /** @var \App\Models\User $user */
       $token = $user->createToken('token')->plainTextToken;
        return response()->json(['message' => 'login successfully', 'user' => $user, 'token' => $token]);
    }

    // public function sendEmail(Request $request)
    // {
    //     $request->validate([
    //         'email' => "required|email"
    //     ]);
    //     $mail = $request->email;
    //     Mail::to($request->email)->send(new EmailCheck($mail));
    //     return response()->json(["msg" => "mail confirmed to reset password"]);
    // }

    public function forgetPassword(Request $request)
    {
        // dd(config('app.frontend_url'));
        $request->validate([
            'email' => 'required|email'
        ]);
        //check if email is exist ot not
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(["msg" => "the email is not exist"], 404);
        }
        //create token
        $token = Str::random(64);
        //store token
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );
        $resetLink = config('app.frontend_url')
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($request->email);
        //make email
        Mail::to($request->email)->send(new EmailCheck($resetLink));
        return response()->json([
            'message' => 'Reset link sent to your email'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();
        if (!$record) {
            return response()->json(['message' => 'Invalid request'], 400);
        }
        if (!Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Invalid token'], 400);
        }
        // updating password
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);
        // delete token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();
        return response()->json(['message' => 'Password reset successfully']);
    }

    // update data of profile
    // public function updateUser(Request $request)
    // {
    //     $user = User::where('email', $request->email)->first();
    //     if (! $user) {
    //         return response()->json(["msg" => "invalid updating"]);
    //     }
    //     $path = $request->file('id_image')->store('ids', 'public');
    //     $path_id = $request->file('national_id')->store('ids', 'public');

    //     $user->update([
    //         'name' => $request->name,
    //         'id_image' => $path,
    //         'national_id' => $path_id,
    //         'nationality' => $request->nationality,
    //         'city' => $request->city,
    //         'street' => $request->street,
    //         'phone' => $request->phone,
    //         'gender' => $request->gender
    //     ]);
    //     return response()->json(["msg" => "updated user successfully"]);
    // }

   public function updateUser(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['msg' => 'Unauthorized'], 401);
        }
        $data = $request->validate([
            'nationality' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'id_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'national_id' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);
        if ($request->hasFile('id_image')) {
            $data['id_image'] = $request->file('id_image')->store('ids', 'public');
        }
        if ($request->hasFile('national_id')) {
            $data['national_id'] = $request->file('national_id')->store('ids', 'public');
        }
        
        $user->update($data);
        return response()->json([
            'msg' => 'User updated successfully',
            'user' => $user
        ], 200);
    }

    //edit user role
    public function updateRole(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|in:none,volunteer,customer'
        ]);
        $user = auth()->user();
        if (!$user) {
            return response()->json(['msg' => 'Unauthorized'], 401);
        }
        /** @var \App\Models\User $user */
        $user->role = $data['role'];
        $user->save();
        return response()->json([
            'msg' => 'User Role updated successfully',
            'user' => $user
        ]);
    }
  
}
