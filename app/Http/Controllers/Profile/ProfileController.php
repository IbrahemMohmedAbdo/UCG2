<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
    
        return response()->json([
            'user' => [
				'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'type' => $user->type,
                'city' => $user->city,
                'birth' => $user->birth,
				'image' => $user->image,
				'personal_id'=>$user->personal_id,
				'commission' => $user->commission,
				'shipping_username' => $user->shipping_username,
				'shipping_password' => $user->shipping_password
            ]
        ], 200);
    }


    public function showUsersPerType($type)
    {
        $users = User::where('type', $type)->select(
			'id',
            'name',
            'email',
            'phone',
            'type',
            'city',
            'birth',
            'image',
			'commission',
			'personal_id',
			'shipping_username',
			'shipping_password',
        )->get();

        return response()->json([
            'users' => $users,
        ]);
    }

    public function updateUsers($id, Request $request)
    {
        $userData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
			'phone' => 'sometimes|regex:/^[0-9]+$/|unique:users',
            'type' => ['sometimes', 'in:innerRepresentative,outerRepresentative,driver,storeManager,accountant'],
            'city' => 'sometimes',
            'birth' => 'sometimes',
            'required_if:type,driver|image|mimes:jpeg,png,jpg,gif',
        ]);
        $user = User::find($id);
        if(!$user){
            return response('User not found', 404);
        }
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif',
            ]);
        $imageName = time() . '_' . $image->getClientOriginalName();
        $imageDirectory = public_path('images');
        $image->move($imageDirectory, $imageName);
        $userData['image'] = 'images/' . $imageName;
        }
		if ($request->shipping_username){
			$userData['commission'] = $request->commission;
			$userData['shipping_username'] = $request->shipping_username;
			$userData['shipping_password'] = $request->shipping_password;
		}
        $user->update($userData);
        
        return response()->json([
            'message' => 'Updated Successfully',
            'user' => $user->only(
				'id',
				'name',
            	'email',
            	'phone',
            	'type',
            	'city',
            	'birth',
            	'image',
				'commission',
				'shipping_username',
				'shipping_password'
			),
        ], 200);
    }

    public function delete($id)
    {
        $user = User::select(
			'id',
            'name',
            'email',
            'phone',
            'type',
            'city',
            'birth',
            'image',
			'commission',
			'shipping_username',
			'shipping_password'
        )->find($id);
        $user->delete();

        return response()->json([
            'message' => 'deleted Successfully',
            'user' => $user,
        ], 200);
    }

    public function searchForUsers($key)
    {

        $users = User::where(function ($query) use ($key) {
            $query->where('name', 'LIKE', '%' . $key . '%')
                  ->orWhere('phone', 'LIKE', '%' . $key . '%');
        })->select(
			'id',
            'name',
            'email',
            'phone',
            'type',
            'city',
            'birth',
            'image',
			'commission',
			'shipping_username',
			'shipping_password'
        )->get();

        return response()->json([
            'user' => $users,
        ], 200);
    }
	
	
	public function searchForUsersWithTypes(Request $request)
    {
        $name = $request->query('name');
        $type = $request->query('type');
        $users = User::where('name', 'LIKE', '%' . $name . '%')
            ->where('type', 'LIKE', '%' . $type . '%')
            ->select('id', 'name', 'email', 'phone', 'type', 'city', 'birth', 'image', 'commission', 'shipping_username', 'shipping_password','is_verified')
            ->get();

        return response()->json(['users' => $users], 200);
    }
	
	public function searchForUsersById(Request $request,$id)
    {
	
        $user = User::where('id', $id)
			->where('appKey',539)
            ->select('id', 'name', 'email', 'phone', 'type', 'city', 'birth', 'image', 'commission', 'shipping_username', 'shipping_password','is_verified')
            ->get();
		if(!$user)
		{
			return (new ApiResponse(200, __('Id not found'), ['User'=>[]]))->send();
		}
		
        return response()->json(['users' => $user], 200);
    }
	
	
	
	
	
	


    public function create(UserRequest $request){
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
        }
        $userData["password"] = Hash::make($userData["password"]);
        $user = User::create($userData);
        $userWallet = new Wallet();
        $userWallet->user_id = $user->id;
        $userWallet->balance = 0;
        $userWallet->save();
        
        return response()->json([
            'message' => 'User Created Successfully',
            'user' => $user,
        ], 201);
    }


    public function resetPassword($id, Request $request)

    {
        $admin = auth()->user();
        if($admin->type == 'admin'){
            $data = $request->validate([
                'password' => 'required|confirmed',
            ]);
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $user->password = Hash::make($data['password']);
            $user->save();

            return response()->json([
                'message' => 'password updated succssess',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'type' => $user->type,
                    'image' => $user->image,
					'commission' => $user->commission,
					'shipping_username' => $user->shipping_username,
					'shipping_password' => $user->shipping_password
                ],
            ]);
        }else{

            return response()->json('You Are Not Admin!');
        }
    }
}
