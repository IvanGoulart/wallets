<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;


class WalletController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $balance = $user->wallet ? $user->wallet->balance : 0;
        return view('wallet.index', [
            'balance' => $balance,
            'transactions' => $user->transactions()->latest()->paginate(10)
        ]);
    }

    public function deposit(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
        $user = auth()->user();
        $amount = $request->amount;

        // Se saldo negativo, depósito acrescenta (mas saldo pode ser negativo? Req diz "caso negativo por algum motivo")
        DB::beginTransaction();
        try {
            if (!$user->wallet) {
                $user->wallet()->create(['balance' => 0]);
                $user->load('wallet');
            }
            $user->wallet->increment('balance', $amount);
            Transaction::create([
                'receiver_id' => $user->id,
                'amount' => $amount,
                'type' => 'deposit',
            ]);
            DB::commit();
            return redirect()->route('wallet.index')->with('success', 'Depósito realizado!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollback();
            return back()->with('error', 'Erro no depósito: ' . $e->getMessage());
        }
    }
}
