<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Repositories\Contracts\WalletRepositoryInterface;
use App\Models\Transaction;
class WalletRepository implements WalletRepositoryInterface
{
    public function getBalance(int $userId): float
    {
        $user = \App\Models\User::find($userId);

        return $user && $user->wallet
            ? $user->wallet->balance
            : 0.0;
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

    public function transfer(int $senderId, int $recipientId, float $amount): array
{
    if ($senderId === $recipientId) {
        return ['success' => false, 'message' => 'Não é possível transferir para si mesmo.'];
    }

    $sender = User::findOrFail($senderId);
    $recipient = User::findOrFail($recipientId);

    // Verifica saldo
    if (!$sender->wallet || $sender->wallet->balance < $amount) {
        return ['success' => false, 'message' => 'Saldo insuficiente para realizar a transferência.'];
    }

    DB::beginTransaction();

    try {
        // Sacar do remetente
        $sender->wallet->balance -= $amount;
        $sender->wallet->save();

        // Depositar no destinatário
        if (!$recipient->wallet) {
            $recipient->wallet()->create(['balance' => $amount]);
        } else {
            $recipient->wallet->balance += $amount;
            $recipient->wallet->save();
        }

        // Registrar transação
        Transaction::create([
            'sender_id' => $senderId,
            'receiver_id' => $recipientId,
            'amount' => $amount,
            'type' => 'transfer',
            'status' => 'completed'
        ]);

        DB::commit();
        return ['success' => true, 'message' => 'Transferência realizada com sucesso!'];

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Erro na transferência: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Ocorreu um erro ao realizar a transferência.'];
    }
}


public function getTransactions(int $userId, int $perPage = 10)
{
    $transactions = \App\Models\Transaction::where('sender_id', $userId)
        ->orWhere('receiver_id', $userId)
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

    // Adiciona a direção para cada transação
    $transactions->getCollection()->transform(function ($t) use ($userId) {
        if ($t->type === 'deposit') {
            $t->direction = 'Depósito';
        } elseif ($t->type === 'transfer') {
            $t->direction = $t->sender_id === $userId ? 'Enviado' : 'Recebido';
        } else {
            $t->direction = $t->type; // outros tipos, se existirem
        }
        return $t;
    });

    return $transactions;
}

    public function getTotalBalance(int $userId): float
    {
        $in = Transaction::where('receiver_id', $userId)
            ->where('status', 'completed')
            ->sum('amount');

        $out = Transaction::where('sender_id', $userId)
            ->where('status', 'completed')
            ->sum('amount');

        return $in - $out;
    }
}
