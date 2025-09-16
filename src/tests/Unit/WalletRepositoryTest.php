<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Wallet;
use App\Repositories\WalletRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Factories\WalletFactory;

class WalletRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private WalletRepository $walletRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletRepository = new WalletRepository();
    }

    public function test_deposit_creates_wallet_if_not_exists(): void
    {
        // cria usuário sem carteira
        $user = User::factory()->create();

        // chama o método que deve criar a wallet e adicionar 100
        $this->walletRepository->deposit($user, 100);

        // verifica no DB
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 100,
        ]);


    }





}
