<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Repositories\Contracts\WalletRepositoryInterface;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    private WalletRepositoryInterface $walletRepository;

    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function create()
    {
        $currentUser = auth()->user(); // pega o objeto User
        $users = User::where('id', '!=', $currentUser->id)->get();

        return view('wallet.transfer', [
            'users' => $users,
            'transactions' => $this->walletRepository->getTransactions($currentUser, 10), // agora passa User
        ]);
    }

    // Executa a transferência
    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $senderId = auth()->id(); // Apenas o ID
        $recipientId = (int) $request->recipient_id;
        $amount = (float) $request->amount;

        // Executa a transferência e recebe o resultado com a mensagem
        $result = $this->walletRepository->transfer($senderId, $recipientId, $amount);

        if ($result['success']) {
            return redirect()->route('wallet.index')->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

public function revertTransaction(Transaction $transaction)
{
    DB::beginTransaction();

    try {
        // Apenas transferências de saída podem ser revertidas
        if ($transaction->type !== 'transfer') {
            throw new \Exception('Apenas transferências enviadas podem ser revertidas.');
        }

        // Carrega remetente e destinatário com suas wallets
        $transaction->load('sender.wallet', 'receiver.wallet');

        // Verifica se remetente e destinatário existem
        if (!$transaction->sender || !$transaction->sender->wallet) {
            throw new \Exception('Carteira do remetente não encontrada.');
        }
        if (!$transaction->receiver || !$transaction->receiver->wallet) {
            throw new \Exception('Carteira do destinatário não encontrada.');
        }

        // Reverte os saldos
        $transaction->sender->wallet->increment('balance', $transaction->amount);
        $transaction->receiver->wallet->decrement('balance', $transaction->amount);

        // Cria uma transação de reversão para o destinatário
        $reversalTransaction = Transaction::create([
            'sender_id' => $transaction->receiver->id,           // Quem perde o valor agora
            'receiver_id' => $transaction->sender->id,           // Quem recebe de volta
            'amount' => $transaction->amount,
            'type' => 'reversal',                                 // Tipo de reversão
            'description' => "Reversão da transferência #{$transaction->id}",
            'reversed_transaction_id' => $transaction->id,       // Referência à transação original
            'status' => 'completed',
        ]);

        // Opcional: marcar a transação original como revertida
        $transaction->status = 'reversed';
        $transaction->save();

        DB::commit();

        return back()->with('success', 'Transação revertida com sucesso!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors('Erro ao reverter a transação: ' . $e->getMessage());
    }
}

}
