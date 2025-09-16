<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;

class WalletRepository implements WalletRepositoryInterface
{
    public function getBalance(User $user): float
    {
        return Wallet::where('user_id', $user->id)->value('balance') ?? 0.0;
    }

    public function deposit(User $user, float $amount): bool
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        $wallet->balance += $amount;
        return $wallet->save();
    }

    public function withdraw(User $user, float $amount): bool
    {
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet || $wallet->balance < $amount) {
            return false;
        }

        $wallet->balance -= $amount;
        return $wallet->save();
    }

    public function transfer(int $senderId, int $recipientId, float $amount): array
    {
        return DB::transaction(function () use ($senderId, $recipientId, $amount) {
            $sender = User::find($senderId);
            $recipient = User::find($recipientId);

            if (!$sender || !$recipient) {
                return ['success' => false, 'message' => 'Usuário(s) não encontrado(s)'];
            }

            if (!$this->withdraw($sender, $amount)) {
                return ['success' => false, 'message' => 'Saldo insuficiente'];
            }

            if (!$this->deposit($recipient, $amount)) {
                throw new \Exception("Falha ao depositar no recebedor");
            }

            // Registrar transação
            Transaction::create([
                'sender_id'    => $senderId,
                'receiver_id' => $recipientId,
                'amount'       => $amount,
                'type'         => 'transfer',
            ]);

            return ['success' => true, 'message' => 'Transferência realizada com sucesso'];
        });
    }

    public function getTransactions(User $user, int $perPage = 10)
    {
        return Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function getTotalBalance(User $user): float
    {
        return Wallet::where('user_id', $user->id)->sum('balance');
    }
}
