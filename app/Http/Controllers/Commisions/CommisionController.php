<?php

namespace App\Http\Controllers\Commisions;

use App\Models\Commision;
use App\Models\User;
use Illuminate\Http\Request;
use App\Response\ApiResponse;
use App\Http\Controllers\Controller;

class CommisionController extends Controller
{
    //


    public function index()
    {
        $commisions=Commision::where('appKey',539)->select('id','type','commision')->get();
        if($commisions)
        {
            return response()->json($commisions);
        }
        else{
            return (new ApiResponse(200, __('Commisions not found.'), []))->send();
        }

    }

    public function store (Request $request)
    {

        $request->validate([
            'type' => 'required|string',
            'commision' => 'required|numeric',

        ]);

        $commission = Commision::create([
            'type' => $request->input('type'),
            'commision' => $request->input('commision'),
            'appKey'=>539,

        ]);

        return response()->json(['message' => 'Commission created successfully', 'data' => $commission], 200);


    }

    public function update(Request $request,$id)
    {


        $commission = Commision::where('appKey', 539)->find($id);


        if (!$commission) {
            return response()->json(['error' => 'Commission not found'], 404);
        }


        $users = User::where('type', $commission->type)->get();
      

        $users->each(function ($user) use ($request) {
            $user->update([
                'commission' => $request->input('commision', $user->commission),
            ]);
        });

        $commission->update([
            'commision' => $request->input('commision', $commission->commission),
        ]);



  return response()->json(['message' => 'Commission updated successfully', 'data' => $commission], 200);





    }


}
