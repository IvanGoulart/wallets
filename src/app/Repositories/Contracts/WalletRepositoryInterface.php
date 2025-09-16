<?php

namespace App\Repositories\Contracts;
use App\Models\User;


interface WalletRepositoryInterface
{
    public function getBalance(User $user): float;

    public function deposit(User $user, float $amount): bool;

    public function withdraw(User $user, float $amount): bool;

    public function transfer(int $senderId, int $recipientId, float $amount): array;

    public function getTransactions(User $user, int $perPage = 10);

    public function getTotalBalance(User $user): float;
}
