<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Repositories\Contracts\WalletRepositoryInterface;

class WalletController extends Controller
{
    private WalletService $walletService;
    private WalletRepositoryInterface $walletRepository;

    public function __construct(
        WalletService $walletService,
        WalletRepositoryInterface $walletRepository
    ) {
        $this->walletService = $walletService;
        $this->walletRepository = $walletRepository;
    }

    public function index()
    {
        $user = auth()->user();

        return view('wallet.index', [
            'balance'       => $this->walletRepository->getBalance($user),
            'totalBalance'  => $this->walletRepository->getTotalBalance($user),
            'transactions'  => $this->walletRepository->getTransactions($user, 10),
        ]);
    }

    /**
     * Realiza um depósito na carteira do usuário
     *
     * @param  mixed $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $user   = auth()->user();
        $amount = $request->input('amount');

        if ($this->walletService->deposit($user, $amount)) {
            return redirect()
                ->route('wallet.index')
                ->with('success', 'Depósito realizado com sucesso!');
        }

        return back()->withErrors([
            'error' => 'Erro no depósito, tente novamente.'
        ]);
    }

    /**
     * Realiza um saque na carteira do usuário
     *
     * @param  mixed $request
     * @return void
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $user   = auth()->user();
        $amount = $request->input('amount');

        if ($this->walletService->withdraw($user, $amount)) {
            return redirect()
                ->route('wallet.index')
                ->with('success', 'Saque realizado com sucesso!');
        }

        return back()->withErrors([
            'error' => 'Saldo insuficiente ou erro no saque.'
        ]);
    }

    /**
     * Realiza uma transferência entre dois usuários
     *
     * @param  mixed $request
     * @return void
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'amount'       => 'required|numeric|min:0.01',
        ]);

        $senderId   = auth()->id();
        $recipientId = $request->input('recipient_id');
        $amount      = $request->input('amount');

        $result = $this->walletService->transfer($senderId, $recipientId, $amount);

        if ($result['success']) {
            return redirect()
                ->route('wallet.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
