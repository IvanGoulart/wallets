<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Services\TransferService;
use App\Services\WalletService;

class TransferController extends Controller
{
    private TransferService $transferService;
    private WalletService $walletService;

    public function __construct(TransferService $transferService, WalletService $walletService)
    {
        $this->transferService = $transferService;
        $this->walletService = $walletService;
    }

    // Método para exibir o formulário / histórico
    public function create()
    {
        $currentUser = auth()->user();
        $transactions = $this->transferService->getTransactions($currentUser, 10);
        $users = \App\Models\User::where('id', '!=', $currentUser->id)->get();

        return view('wallet.transfer', [
            'users' => $users,
            'transactions' => $transactions,
        ]);
    }

    // Método para executar transferência
    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $sender = auth()->user();
        $recipient = \App\Models\User::findOrFail($request->recipient_id);
        $amount = (float) $request->amount;

        $result = $this->transferService->transfer($sender, $recipient, $amount);

        if ($result['success']) {
            return redirect()->route('wallet.index')->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    // Método público para reverter transação
    public function revert(Transaction $transaction)
    {
        try {
            $this->transferService->revertTransaction($transaction);
            return back()->with('success', 'Transação revertida com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors('Erro ao reverter a transação: ' . $e->getMessage());
        }
    }

    // Método para relatório detalhado
    public function detailedReport()
    {
        $user = auth()->user();
        $transactions = $this->walletService->getUserDetailedReport($user, 20);

        return view('wallet.detailed_report', compact('transactions'));
    }
}
