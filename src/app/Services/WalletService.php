<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Repositories\WalletRepository;

class WalletService
{
    public function __construct(
        private WalletRepository $walletRepository
    ) {}

    public function deposit(User $user, float $amount): bool
    {
        $wallet = $this->walletRepository->createWalletIfNotExists($user);

        DB::beginTransaction();
        try {
            $this->walletRepository->incrementBalance($wallet, $amount);

            $this->walletRepository->createTransaction([
                'sender_id'   => null,
                'receiver_id' => $user->id,
                'amount'      => $amount,
                'type'        => 'deposit',
                'status'      => 'completed',
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function withdraw(User $user, float $amount): bool
    {
        $wallet = $user->wallet;
        if (!$wallet) {
            return false;
        }

        return $this->walletRepository->decrementBalance($wallet, $amount);
    }

    public function transfer(int $senderId, int $recipientId, float $amount): array
    {
        if ($senderId === $recipientId) {
            return ['success' => false, 'message' => 'Não é possível transferir para si mesmo.'];
        }

        $sender = User::findOrFail($senderId);
        $recipient = User::findOrFail($recipientId);

        if (!$sender->wallet || $sender->wallet->balance < $amount) {
            return ['success' => false, 'message' => 'Saldo insuficiente.'];
        }

        DB::beginTransaction();
        try {
            // movimenta carteiras
            $this->walletRepository->decrementBalance($sender->wallet, $amount);
            $recipientWallet = $this->walletRepository->createWalletIfNotExists($recipient);
            $this->walletRepository->incrementBalance($recipientWallet, $amount);

            // cria transações
            $transferOut = $this->walletRepository->createTransaction([
                'sender_id' => $senderId,
                'receiver_id' => $recipientId,
                'amount' => $amount,
                'type' => 'transfer',
                'status' => 'completed',
                'description' => "Transferência enviada para {$recipient->name} ({$recipient->email})"
            ]);

            $this->walletRepository->createTransaction([
                'sender_id' => $senderId,
                'receiver_id' => $recipientId,
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'completed',
                'description' => "Transferência recebida de {$sender->name} ({$sender->email})",
                'reversed_transaction_id' => $transferOut->id,
            ]);

            DB::commit();
            return ['success' => true, 'message' => 'Transferência realizada com sucesso!'];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro na transferência: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao realizar a transferência.'];
        }
    }
}
