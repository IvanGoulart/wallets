<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function __construct(private WalletRepositoryInterface $walletRepository) {}

    /**
     * Realiza uma transferência entre dois usuários
     *
     * @param  mixed $sender
     * @param  mixed $recipient
     * @param  mixed $amount
     * @return array
     */
    public function transfer(User $sender, User $recipient, float $amount): array
    {
        if ($sender->id === $recipient->id) {
            return ['success' => false, 'message' => 'Não é possível transferir para si mesmo.'];
        }

        if (!$sender->wallet || $sender->wallet->balance < $amount) {
            return ['success' => false, 'message' => 'Saldo insuficiente.'];
        }

        DB::beginTransaction();
        try {
            // Débito do remetente
            $this->walletRepository->withdraw($sender, $amount);

            // Cria carteira do destinatário se não existir e credita
            $this->walletRepository->deposit($recipient, $amount);

            // Cria apenas uma transação do tipo 'transfer'
            $transaction = $this->walletRepository->createTransaction([
                'sender_id' => $sender->id,
                'receiver_id' => $recipient->id,
                'amount' => $amount,
                'type' => 'transfer',
                'status' => 'completed',
                'description' => "Transferência de {$sender->name} para {$recipient->name}",
            ]);

            DB::commit();

            return ['success' => true, 'message' => 'Transferência realizada com sucesso!'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Erro ao realizar a transferência: ' . $e->getMessage()];
        }
    }

    /**
     * Reverte uma transação de transferência
     *
     * @param  mixed $transaction
     * @return bool
     * @throws \Exception
     */
    public function revertTransaction(Transaction $transaction): bool
    {
        if ($transaction->type !== 'transfer') {
            throw new \Exception('Apenas transferências podem ser revertidas.');
        }

        DB::beginTransaction();
        try {
            $sender = $transaction->sender;
            $receiver = $transaction->receiver;

            if (!$sender || !$sender->wallet || !$receiver || !$receiver->wallet) {
                throw new \Exception('Carteira(s) não encontrada(s).');
            }

            // Reverte saldos
            $this->walletRepository->deposit($sender, $transaction->amount);
            $this->walletRepository->withdraw($receiver, $transaction->amount);

            // Marca transação como revertida
            $transaction->status = 'reversed';
            $transaction->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtém as transações do usuário
     *
     * @param  mixed $user
     * @param  mixed $perPage
     * @return void
     */
    public function getTransactions(User $user, int $perPage = 10)
    {
        $transactions = Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Adiciona a direção correta para cada usuário
        $transactions->getCollection()->transform(function ($t) use ($user) {
            if ($t->type === 'deposit') {
                $t->direction = 'Depósito';
            } elseif ($t->type === 'transfer') {
                $t->direction = $t->sender_id === $user->id ? 'Enviado' : 'Recebido';
            } else {
                $t->direction = $t->type;
            }
            return $t;
        });

        return $transactions;
    }


}
