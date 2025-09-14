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
        return view('wallet.index', [
            'balance' => $this->walletRepository->getBalance(auth()->user()),
            'transactions' => $this->walletRepository->getTransactions(auth()->user(), 10),
        ]);
    }

    public function deposit(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
            $amount = $request->input('amount'); // <- pegar o valor do form



        if ($this->walletRepository->deposit(auth()->id(), $amount)) {
            return redirect()->route('wallet.index')->with('success', 'Depósito realizado!');
        }

        return back()->with('error', 'Erro no depósito, tente novamente.');
    }
}
