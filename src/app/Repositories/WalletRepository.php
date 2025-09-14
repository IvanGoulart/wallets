<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Repositories\Contracts\WalletRepositoryInterface;
use App\Models\Transaction;
class WalletRepository implements WalletRepositoryInterface
{
    public function getBalance(User $user): float
    {
        return $user->wallet ? $user->wallet->balance : 0;
    }

    public function deposit(int $userId, float $amount): bool
    {
        $user = User::findOrFail($userId);

        if (!$user->wallet) {
            $user->wallet()->create(['balance' => 0]);
        }

        $user->wallet->balance += $amount;
        $saved = $user->wallet->save();

        if ($saved) {
            Transaction::create([
                'sender_id' => null,
                'receiver_id' => $userId,
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'completed'
            ]);
        }

        return $saved;
    }


public function withdraw(int $userId, float $amount): bool
{
    $wallet = \App\Models\Wallet::where('user_id', $userId)->first();

    if (!$wallet || $wallet->balance < $amount) {
        return false;
    }

    $wallet->balance -= $amount;
    return $wallet->save();
}


public function transfer(int $senderId, int $recipientId, float $amount): bool
{
    if ($senderId === $recipientId) {
        return false; // não permite auto-transferência
    }

    DB::beginTransaction();
    try {
        // Sacar do remetente
        if (!$this->withdraw($senderId, $amount)) {
            DB::rollBack();
            return false;
        }

        // Depositar no destinatário
        if (!$this->deposit($recipientId, $amount)) {
            DB::rollBack();
            return false;
        }

        // Registrar transações
        \App\Models\Transaction::create([
            'sender_id' => $senderId,
            'receiver_id' => $recipientId,
            'amount' => $amount,
            'type' => 'transfer',
            'status' => 'completed',
        ]);

        \App\Models\Transaction::create([
            'sender_id' => $senderId,
            'receiver_id' => $recipientId,
            'amount' => $amount,
            'type' => 'transfer',
            'status' => 'completed',
        ]);

        DB::commit();
        return true;
    } catch (\Exception $e) {
        DB::rollBack();
        return false;
    }
}

public function getTransactions(int $userId, int $perPage = 10)
{
    return \App\Models\Transaction::where('sender_id', $userId)
        ->orWhere('receiver_id', $userId)
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
}

}
