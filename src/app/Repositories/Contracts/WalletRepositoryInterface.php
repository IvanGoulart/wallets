<?php

namespace App\Repositories\Contracts;
use App\Models\User;


interface WalletRepositoryInterface
{
 public function getBalance(User $user): float;

    public function deposit(int $userId, float $amount): bool;

    public function withdraw(int $userId, float $amount): bool;

    public function transfer(int $senderId, int $recipientId, float $amount): bool;

    public function getTransactions(int $userId, int $perPage = 10);

}
