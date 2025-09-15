<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\WalletRepositoryInterface;

class WalletController extends Controller
{
    private WalletRepositoryInterface $walletRepository;

    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function index()
    {
        $userId = auth()->id();

        return view('wallet.index', [
            'balance' => $this->walletRepository->getBalance($userId),
            'totalBalance' => $this->walletRepository->getTotalBalance($userId), // saldo via transações
            'transactions' => $this->walletRepository->getTransactions($userId, 10),
        ]);
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $userId = auth()->id();
        $amount = $request->input('amount');

        if ($this->walletRepository->deposit($userId, $amount)) {
            return redirect()
                ->route('wallet.index')
                ->with('success', 'Depósito realizado!');
        }

        return back()->withErrors([
            'error' => 'Erro no depósito, tente novamente.'
        ]);
    }
}
