<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Repositories\Contracts\WalletRepositoryInterface;

class TransferController extends Controller
{
    private WalletRepositoryInterface $walletRepository;

    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    // Formulário de transferência
    public function create()
    {
        $users = User::where('id', '!=', auth()->id())->get();

        return view('wallet.transfer', [
            'users' => $users,
            'transactions' => $this->walletRepository->getTransactions(auth()->id(), 10), // Passando o ID
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

}
