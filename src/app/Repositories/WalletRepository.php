<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;

class WalletRepository implements WalletRepositoryInterface
{

    /**
     * Obtém o saldo da carteira do usuário
     *
     * @param  mixed $user
     * @return float
     */
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

    /**
     * Realiza um saque na carteira do usuário
     *
     * @param  mixed $user
     * @param  mixed $amount
     * @return bool
     */
    public function withdraw(User $user, float $amount): bool
    {
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet || $wallet->balance < $amount) {
            return false;
        }

        $wallet->balance -= $amount;
        return $wallet->save();
    }


    /**
     * Realiza uma transferência entre dois usuários
     *
     * @param  mixed $senderId
     * @param  mixed $recipientId
     * @param  mixed $amount
     * @return array
     */
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

    /**
     * Obtém as transações do usuário
     *
     * @param  mixed $user
     * @param  mixed $perPage
     * @return void
     */
    public function getTransactions(User $user, int $perPage = 10)
    {
        $transactions = \App\Models\Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Adiciona a direção apenas para exibição, sem duplicar registros
        $transactions->getCollection()->transform(function ($t) use ($user) {

            // Define a direção padrão
            if ($t->type === 'deposit') {
                $t->direction = 'Depósito';
            } elseif ($t->type === 'transfer') {
                $t->direction = $t->sender_id === $user->id ? 'Enviado' : 'Recebido';
            } else {
                $t->direction = $t->type;
            }

            // Marca transações revertidas
            if ($t->status === 'reversed') {
                $t->direction .= ' (Revertida)';
                $t->description = $t->description ?: 'Transação revertida';
            }

            return $t;
        });

        return $transactions;
    }

    /**
     * Calcula o saldo total do usuário (caso tenha múltiplas carteiras)
     *
     * @param  mixed $user
     * @return float
     */
    public function getTotalBalance(User $user): float
    {
        return Wallet::where('user_id', $user->id)->sum('balance');
    }

    /**
     * Cria uma carteira para o usuário se não existir
     *
     * @param  mixed $user
     * @return Wallet
     */
    public function createWalletIfNotExists(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );
    }


    /**
     * Incrementa o saldo da carteira do usuário
     *
     * @param  mixed $user
     * @param  mixed $amount
     * @return bool
     */
    public function incrementBalance(User $user, float $amount): bool
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        $wallet->balance += $amount;
        return $wallet->save();
    }


    /**
     * Cria uma nova transação
     *
     * @param  mixed $data
     * @return Transaction
     */
    public function createTransaction(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function decrementBalance(User $user, float $amount): bool
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        if ($wallet->balance < $amount) {
            return false; // saldo insuficiente
        }

        $wallet->balance -= $amount;
        return $wallet->save();
    }

    /**
     * Relatório detalhado de transações com saldo acumulado
     *
     * @param  mixed $user
     * @param  mixed $perPage
     * @return void
     */
    public function getDetailedReport(User $user, int $perPage = 20)
    {
        // Busca todas transações do usuário em ordem cronológica ASC
        $transactions = Transaction::with(['sender', 'receiver'])
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $runningBalance = 0;

        // Calcula saldo acumulado e formata dados
    $transactions->transform(function ($t) use (&$runningBalance, $user) {
        $amount = $t->amount;
        $isReversed = $t->status === 'reversed';

        $t->original_amount = number_format($amount, 2, ',', '.'); // mostra valor original

        // Marca direção da transação
        if ($t->type === 'deposit') {
            $t->direction = 'Depósito';
        } elseif ($t->type === 'transfer') {
            $t->direction = $t->sender_id === $user->id ? 'Enviado' : 'Recebido';
        } else {
            $t->direction = $t->direction ?? 'Transação';
        }

        // Ajusta saldo apenas se não for revertida
        if (!$isReversed) {
            if ($t->type === 'deposit') {
                $runningBalance += $amount;
            } elseif ($t->type === 'transfer') {
                $runningBalance += $t->sender_id === $user->id ? -$amount : $amount;
            }
        } else {
            // marca como revertida
            $t->direction .= ' (Revertida)';
            $t->description = $t->description ?? 'Transação revertida';
        }

        $t->balance_after = $runningBalance;
        $t->formatted_amount = number_format($amount, 2, ',', '.');
        $t->formatted_date = $t->created_at->format('d/m/Y H:i');

        $t->counterparty = $t->sender_id === $user->id
            ? ($t->receiver->name ?? '-')
            : ($t->sender->name ?? '-');

        return $t;
    });

    // Exibe na ordem DESC e paginar manualmente
    $paginated = $transactions->reverse()->values();
    $currentPage = request()->get('page', 1);
    $items = $paginated->slice(($currentPage - 1) * $perPage, $perPage);

    return new \Illuminate\Pagination\LengthAwarePaginator(
        $items,
        $transactions->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'query' => request()->query()]
    );
}


}
