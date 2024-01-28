<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function createWallet($userId, $initialBalance = 0)
    {
        try {
            $wallet = Wallet::create([
                'user_id' => $userId,
                'balance' => $initialBalance,
            ]);

            $this->logTransaction($wallet->id, $initialBalance, 'Initial balance', 'deposit');

            return [
                'status' => true,
                'msg' => 'Wallet created successfully.',
                'data' => $wallet,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => 'Error creating wallet: ' . $e->getMessage(),
            ];
        }
    }

    public function getWalletBalance($userId)
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return [
                'status' => false,
                'msg' => 'User does not have a wallet.',
            ];
        }

        return [
            'status' => true,
            'msg' => 'Wallet balance retrieved successfully.',
            'data' => $wallet->balance,
        ];
    }

    public function addFunds($userId, $amount, $description = null, $orderId = null)
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return [
                'status' => false,
                'msg' => 'User does not have a wallet.',
            ];
        }

        try {
            DB::transaction(function () use ($wallet, $amount, $description, $orderId) {
                $wallet->balance += $amount;
                $wallet->save();

                $this->logTransaction($wallet->id, $amount, $description ?? 'اضافة رصيد', 'deposit', $orderId);
            });

            return [
                'status' => true,
                'msg' => 'Funds added successfully.',
                'data' => $wallet->balance,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => 'Error adding funds: ' . $e->getMessage(),
            ];
        }
    }

    public function subFunds($userId, $amount, $description = null, $orderId = null)
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return [
                'status' => false,
                'msg' => 'User does not have a wallet.',
            ];
        }

        // if ($wallet->balance < $amount) {
        //     return [
        //         'status' => false,
        //         'msg' => 'Insufficient balance for the operation.',
        //     ];
        // }

        try {
            DB::transaction(function () use ($wallet, $amount, $description, $orderId) {
                $wallet->balance -= $amount;
                $wallet->save();

                $this->logTransaction($wallet->id, -$amount, $description ?? 'طرح رصيد', 'withdraw', $orderId);
            });

            return [
                'status' => true,
                'msg' => 'Funds subtracted successfully.',
                'data' => $wallet->balance,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => 'Error subtracting funds: ' . $e->getMessage(),
            ];
        }
    }

    public function transferFunds($senderId, $receiverId, $amount, $description = null, $orderId = null)
    {
        $senderWallet = Wallet::where('user_id', $senderId)->first();
        $receiverWallet = Wallet::where('user_id', $receiverId)->first();

        if (!$senderWallet || !$receiverWallet) {
            return [
                'status' => false,
                'msg' => 'Sender or receiver does not have a wallet.',
            ];
        }

        if ($senderWallet->balance < $amount) {
            return [
                'status' => false,
                'msg' => 'Insufficient balance for the transfer.',
            ];
        }

        try {
            DB::transaction(function () use ($senderWallet, $receiverWallet, $amount, $description, $orderId) {
                $senderWallet->balance -= $amount;
                $senderWallet->save();

                $receiverWallet->balance += $amount;
                $receiverWallet->save();

                $this->logTransaction($senderWallet->id, -$amount, $description ?? "تحويل الى ".$receiverWallet->user->name, 'withdraw', $orderId);
                $this->logTransaction($receiverWallet->id, $amount, $description ?? "تحويل من ".$senderWallet->user->name, 'deposit', $orderId);
            });

            return [
                'status' => true,
                'msg' => 'Funds transferred successfully.',
                'data' => $senderWallet->balance,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => 'Error transferring funds: ' . $e->getMessage(),
            ];
        }
    }

    public function transferAllFunds($senderId, $receiverId, $description = null, $orderId = null)
    {
        $senderWallet = Wallet::where('user_id', $senderId)->first();
        $receiverWallet = Wallet::where('user_id', $receiverId)->first();

        if (!$senderWallet || !$receiverWallet) {
            return [
                'status' => false,
                'msg' => 'Sender or receiver does not have a wallet.',
            ];
        }
        if($senderWallet->balance < 0){
            // Swap variables $senderId and $receiverId
            $tempId = $senderId;
            $senderId = $receiverId;
            $receiverId = $tempId;

            // Swap variables $senderWallet and $receiverWallet
            $tempWallet = $senderWallet;
            $senderWallet = $receiverWallet;
            $receiverWallet = $tempWallet;
        }

        try {
            $amount = $senderWallet->balance;

            DB::transaction(function () use ($senderWallet, $receiverWallet, $description, $orderId, $amount) {
                // Update balances
                $senderWallet->balance -= $amount;
                $senderWallet->save();

                $receiverWallet->balance += $amount;
                $receiverWallet->save();

                // Log transactions
                $this->logTransaction($senderWallet->id, -$amount, $description ?? "تحويل الى ".$receiverWallet->user->name, 'withdraw', $orderId);
                $this->logTransaction($receiverWallet->id, $amount, $description ?? "تحويل من ".$senderWallet->user->name, 'deposit', $orderId);
            });

            return [
                'status' => true,
                'msg' => 'All funds transferred successfully.',
                'data' => $senderWallet->balance,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg' => 'Error transferring all funds: ' . $e->getMessage(),
            ];
        }
    }

    public function getTransactionHistory($userId)
    {
       
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return [
                'status' => false,
                'msg' => 'User does not have a wallet.',
            ];
        }

        return [
            'status' => true,
            'msg' => 'Transaction history retrieved successfully.',
            'data' => Transaction::where('wallet_id', $wallet->id)->get(),
        ];
    }

    private function logTransaction($walletId, $amount, $description, $type = 'withdraw', $orderId = null)
    {
        Transaction::create([
            'wallet_id' => $walletId,
            'amount' => $amount,
            'description' => $description,
            'type' => $type,
            'order_id' => $orderId,
        ]);
    }
}
