<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
	public function allWallet()
	{
		$wallets = Wallet::with('user')->where('appKey', 539)->get();
		$walletData = $wallets->map(function ($wallet) {
			$user = $wallet->user;

			return [
				'id' => $wallet->id,
				'name' => $user ? $user->name : null,
				'user_id' => $wallet->user_id,
				'balance' => $wallet->balance,
				'created_at' => $wallet->created_at,
				'updated_at' => $wallet->updated_at,
			];
		});

		return response()->json([
			'wallets' => $walletData,
		]);
	}
	
	
    public function userWallet()
    {
        $wallet = auth()->user()->wallet;
        return response()->json([
            'wallet' => [
                'balance' => $wallet->balance,
            ],
        ]);
    }
	
	
	public function walletByType($type)
    {
        $wallets = User::where('appKey', 539)
                    ->where('type', $type)
                    ->with('wallet')
                    ->get()
                    ->pluck('wallet', 'id')
                    ->flatten();

        $wallets = $wallets->map(function ($wallet) {
            if($wallet){
                $user = User::where('id', $wallet->user_id)->first();
                return [
                    'id' => $wallet->id,
                    'name' => $user->name,
					'user_id' => $wallet->user_id,
                    'balance' => $wallet->balance,
                    'created_at' => $wallet->created_at,
                    'updated_at' => $wallet->updated_at,
                ];
            }
        });
		
        return response()->json([
            'wallets' => $wallets,
        ]);
    }
    

    public function transferWalletFunds(User $user)
    {
        $loggedInId = auth()->user()->id;
        $loggedIn = User::find($loggedInId);
        if ($loggedIn->type == 'accountant' && $user->type == 'driver') {
            DB::transaction(function () use ($loggedIn, $user) {
                $loggedIn->wallet->balance += $user->wallet->balance;
				$loggedIn->wallet->save();
                $user->wallet->balance = 0;
                $user->wallet->save();
            });

            return response()->json([
                'transaction for diver successfully',
                'accountant wallet' => $loggedIn->wallet->balance,
                'driver' => $user->wallet->balance,
            ], 200);
        }elseif ($loggedIn->type == 'accountant' && $user->type == 'innerRepresentative') {
                $user->wallet->balance = 0;
                $user->wallet->save();

                return response()->json([
                    'transaction for innerRepresentative successfully',
                    'accountant wallet' => $loggedIn->wallet->balance,
                    'driver' => $user->wallet->balance,
                ], 200);
        }
    }
}
