<?php

namespace App\Http\Controllers\Transactions;

use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }


    public function transferAllFundsUser(Request $request)
    {
        $senderUserId = $request->input('sender_user_id');
        $receiverUserId = $request->input('receiver_user_id');
        $description = $request->input('description', null);
        $orderId = $request->input('order_id', null);


         $response = $this->walletService->transferAllFunds($senderUserId, $receiverUserId, $description, $orderId);
         $msg = $response['status'];
         if($response['status']){

            return response()->json(['data' => $response], 200);

         }else{
            return $msg;
         }


    }

    public function subFundsDriver(Request $request)
    {
        $userId = $request->input('driver_id');
        $amount = $request->input('amount');
        $description = $request->input('description', null);
        $orderId = $request->input('order_id', null);

        try {
            $response = $this->walletService->subFunds($userId, $amount, $description, $orderId);

            return response()->json(['message' => 'Funds subtracted successfully', 'data' => $response], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addFundsRepresintive(Request $request)
    {
        $userId = $request->input('Represintive_id');
        $amount = $request->input('amount');
        $description = $request->input('description', null);
        $orderId = $request->input('order_id', null);

        try {
            $response = $this->walletService->addFunds($userId, $amount, $description, $orderId);

            return response()->json(['data' => $response], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function getTransactionHistory(Request $request)
    {

        $userId = $request->input('user_id');


        try {
            $transactionHistory = $this->walletService->getTransactionHistory($userId);

            return response()->json(['data' => $transactionHistory], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



}
