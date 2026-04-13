<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    // get all users
    public function index()
    {
        $users = User::latest()->get();
        return response()->json($users);
    }

    // delete user
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    // suspend user
    public function suspend($id)
    {
        $user = User::findOrFail($id);
        $user->is_suspended = true;
        $user->save();

        return response()->json(['message' => 'User suspended']);
    }

    // unsuspend user
    public function unsuspend($id)
    {
        $user = User::findOrFail($id);
        $user->is_suspended = false;
        $user->save();

        return response()->json(['message' => 'User unsuspended']);
    }

    //  toggle activation
    public function toggleActivation($id)
    {
        $user = User::findOrFail($id);

        $user->activation = !$user->activation; // Toggle the activation status
        $user->save();

        return response()->json([
            'message' => 'User activation updated',
            'activation' => $user->activation
        ]);
    }
    public function updateNationalId(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'national_id' => 'required|string|max:255',
        ]);

        $user->national_id = $data['national_id'];
        $user->save();

        return response()->json([
            'message' => 'National ID updated successfully',
            'user' => $user
        ]);
    }
}
