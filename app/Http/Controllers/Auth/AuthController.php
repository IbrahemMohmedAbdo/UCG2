<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Commision;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(UserRequest $request)
    {
       $userData = $request->validated();

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif',
        ]);

        $imageName = time() . '_' . $image->getClientOriginalName();
        $imageDirectory = public_path('images');
        $image->move($imageDirectory, $imageName);
        $userData['image'] = 'images/' . $imageName;
    } else {
        $userData['image'] = 'images/main.png';
    }
			 
    if ($request->hasFile('personal_id')) {
        $image = $request->file('personal_id');
        $request->validate([
            'personal_id' => 'image|mimes:jpeg,png,jpg,gif',
        ]);

        $imageName = time() . '_' . $image->getClientOriginalName();
        $imageDirectory = public_path('images');
        $image->move($imageDirectory, $imageName);
        $userData['personal_id'] = 'images/' . $imageName;
    } else {
        $userData['personal_id'] = 'images/main.png';
    }

    $userData["password"] = Hash::make($userData["password"]);

    // Find the role based on the provided 'type'
    $role = Role::where('name', $request->input('type'))->first();

    if (!$role) {
        return response()->json(['error' => 'Invalid user type'], 422);
    }
	if ($request->shipping_username){
	//	$userData['commission'] = $request->commission;
		$userData['shipping_username'] = $request->shipping_username;
		$userData['shipping_password'] = $request->shipping_password;
	}
    $user = User::create($userData);
         $user->assignRole($request->input('type'));  // Assign the role to the user

    $user->appKey = 539;
    $user->save();
	$wallet = new Wallet();
	$wallet->user_id = $user->id;
	$wallet->appKey = 539;
	$wallet->save();
		 if ($user->save()) {
        $type = $user->type;
        $userCommission = $user->commission;

        $listTypes = ["innerRepresentative", "outerRepresentative", "innerRepresentativesManager", "outerRepresentativesManager"];

        if (in_array($type, $listTypes)) {

            $commissions = Commision::where('type', $type)->get();


                $typeCommissions = $commissions->sum('commision');
                $userCommission += $typeCommissions;


                $user->commission = $userCommission;
                $user->save();

        }
    }
		

    return response()->json([
        'message' => 'User Created Successfully',
        'user' => $user,
    ], 201);
    }
	
	
	 public function registerAccounts(UserRequest $request)
    {
        if (!Auth::check()) {
            return (new ApiResponse(401, __('Unauthenticated'), []))->send();
        }
       $userData = $request->validated();

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif',
        ]);

        $imageName = time() . '_' . $image->getClientOriginalName();
        $imageDirectory = public_path('images');
        $image->move($imageDirectory, $imageName);
        $userData['image'] = 'images/' . $imageName;
    } else {
        $userData['image'] = 'images/main.png';
    }
		 
    if ($request->hasFile('personal_id')) {
        $image = $request->file('personal_id');
        $request->validate([
            'personal_id' => 'image|mimes:jpeg,png,jpg,gif',
        ]);

        $imageName = time() . '_' . $image->getClientOriginalName();
        $imageDirectory = public_path('images');
        $image->move($imageDirectory, $imageName);
        $userData['personal_id'] = 'images/' . $imageName;
    } else {
        $userData['personal_id'] = 'images/main.png';
    }
	/*
	if ($request->commission){
		$userData['commission'] = $request->commission;
	}
		 */
		 
	if ($request->shipping_username){
		$userData['shipping_username'] = $request->shipping_username;
		$userData['shipping_password'] = $request->shipping_password;
	}
		 
    $userData["password"] = Hash::make($userData["password"]);

    // Find the role based on the provided 'type'
    $role = Role::where('name', $request->input('type'))->first();

    if (!$role) {
        return response()->json(['error' => 'Invalid user type'], 422);
    }
    $user = User::create($userData);
    $user->assignRole($request->input('type'));  // Assign the role to the user
	

	$user->appKey = 539;
	$user->save();
	$wallet = new Wallet();
	$wallet->user_id = $user->id;
	$wallet->appKey = 539;
	$wallet->save();
		  if ($user->save()) {
        $type = $user->type;
        $userCommission = $user->commission;

        $listTypes = ["innerRepresentative", "outerRepresentative", "innerRepresentativesManager", "outerRepresentativesManager"];

        if (in_array($type, $listTypes)) {

            $commissions = Commision::where('type', $type)->get();


                $typeCommissions = $commissions->sum('commision');
                $userCommission += $typeCommissions;


                $user->commission = $userCommission;
                $user->save();

        }
    }

	return response()->json([
	   'message' => 'User Created Successfully',
	   'user' => $user,
	], 201);
	}

    public function login(Request $request)
    {
        $userData = $request->only('phone', 'password');
        if(!Auth::attempt($userData)){
            return response()->json(['message' => 'Login failed'], 401);  
        }
        $user = Auth::user();
		
		  if (!$user->is_verified) {
        return response()->json(['message' => 'User not verified'], 401);
    }
        $token = $user->createToken('authToken')->plainTextToken;
        
        return response()->json([
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'type' => $user->type,
			'personal_id'=>$user->personal_id,
			'commission' => $user->commossion,
			'shipping_name' => $user->shipping_username,
			'shipping_password' => $user->shipping_password
        ],
        'token' => $token],200);

    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful'], 200);
    }
	
	
	public function verifyAccount(Request $request)
{
		
		$user_id=$request->user_id;
		
    $user = User::where('id', $user_id) 
                ->where('is_verified', 0) 
                ->first();

    if (!$user) {
        return response()->json(['message' => 'User is already verified or does not exist'], 422);
    }

    $user->is_verified=1;
    $user->save();

    return response()->json(['message' => 'Account verified successfully'], 200);
}
	
	
	
	
	
	
	
}
