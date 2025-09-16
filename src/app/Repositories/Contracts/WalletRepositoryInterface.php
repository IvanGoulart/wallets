<?php

namespace App\Repositories\Contracts;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;


interface WalletRepositoryInterface
{
    /**
     * Obtém o saldo da carteira do usuário
     *
     * @param  mixed $user
     * @return float
     */
    public function getBalance(User $user): float;

    /**
     * Realiza um depósito na carteira do usuário
     *
     * @param  mixed $user
     * @param  mixed $amount
     * @return bool
     */
    public function deposit(User $user, float $amount): bool;

    /**
     * Realiza um saque na carteira do usuário
     *
     * @param  mixed $user
     * @param  mixed $amount
     * @return bool
     */

    public function withdraw(User $user, float $amount): bool;
    /**
     * Realiza uma transferência entre dois usuários
     *
     * @param  mixed $senderId
     * @param  mixed $recipientId
     * @param  mixed $amount
     * @return array
     */
    public function transfer(int $senderId, int $recipientId, float $amount): array;

    /**
     * Traz as transações do usuário
     *
     * @param  mixed $user
     * @param  mixed $perPage
     * @return void
     */
    public function getTransactions(User $user, int $perPage = 10);

    /**
     * Calcula o saldo total do usuário (caso tenha múltiplas carteiras)
     *
     * @param  mixed $user
     * @return float
     */
    public function getTotalBalance(User $user): float;

    /**
     * Cria uma nova carteira para o usuário, caso não exista
     *
     * @param  mixed $user
     * @return Wallet
     */
    public function createWalletIfNotExists(User $user): Wallet;

    /**
     * Incrementa o saldo da carteira do usuário
     *
     * @param  mixed $user
     * @param  mixed $amount
     * @return bool
     */
    public function incrementBalance(User $user, float $amount): bool;

    /**
     * Cria uma nova transação
     *
     * @param  mixed $data
     * @return Transaction
     */
    public function createTransaction(array $data): Transaction;

    /**
     * Decrementa o saldo da carteira do usuário
     *
     * @param  mixed $user
     * @param  mixed $amount
     * @return bool
     */
    public function decrementBalance(User $user, float $amount): bool;

    /**
     * Obtém o relatório detalhado do usuário
     *
     * @param  mixed $user
     * @param  mixed $perPage
     * @return void
     */
    public function getDetailedReport(User $user, int $perPage = 20);
}
