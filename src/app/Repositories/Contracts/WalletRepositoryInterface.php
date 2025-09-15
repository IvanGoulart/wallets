<?php

namespace App\Repositories\Contracts;
use App\Models\User;


interface WalletRepositoryInterface
{
 public function getBalance(int $userId): float;

    public function deposit(int $userId, float $amount): bool;

    public function withdraw(int $userId, float $amount): bool;

    public function transfer(int $senderId, int $recipientId, float $amount): array;

    public function getTransactions(int $userId, int $perPage = 10);

    public function getTotalBalance(int $userId): float;

}
