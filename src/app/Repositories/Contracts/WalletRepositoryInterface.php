<?php

namespace App\Repositories\Contracts;
use App\Models\User;


interface WalletRepositoryInterface
{
 public function getBalance(User $user): float;

    public function deposit(int $userId, float $amount): bool;

    public function withdraw(User $user, float $amount): bool;

    public function transfer(User $sender, User $recipient, float $amount): bool;

    public function getTransactions(User $user, int $perPage = 10);
}
