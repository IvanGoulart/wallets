<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Models\Transaction;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\DB;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');

      // Transferências
    Route::get('/wallet/transfer', [TransferController::class, 'create'])->name('wallet.transfer.form');
    Route::post('/wallet/transfer', [TransferController::class, 'store'])->name('wallet.transfer');
    //desfazer transação
    Route::patch('/wallet/transaction/revert/{transaction}', [TransferController::class, 'revert'])
    ->name('wallet.transaction.revert');
    // Relatório detalhado de transações do usuário
    Route::get('/wallet/detailed-report', [TransferController::class, 'detailedReport'])
        ->name('wallet.detailed_report');
});


require __DIR__.'/auth.php';
