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


    public function withdraw(User $user, float $amount): bool
    {
        if (!$user->wallet || $user->wallet->balance < $amount) {
            return false;
        }

        $user->wallet->balance -= $amount;
        return $user->wallet->save();
    }

    public function transfer(User $sender, User $recipient, float $amount): bool
    {
        if ($sender->id === $recipient->id) {
            return false;
        }

        DB::beginTransaction();
        try {
            if (!$this->withdraw($sender, $amount)) {
                DB::rollBack();
                return false;
            }

            $this->deposit($recipient, $amount);

            // Registrar transações (opcional)
            $sender->transactions()->create([
                'type' => 'transfer_out',
                'amount' => $amount,
                'description' => "Transferência para {$recipient->email}"
            ]);

            $recipient->transactions()->create([
                'type' => 'transfer_in',
                'amount' => $amount,
                'description' => "Recebido de {$sender->email}"
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
    public function getTransactions(User $user, int $perPage = 10)
    {
        return $user->transactions()->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
