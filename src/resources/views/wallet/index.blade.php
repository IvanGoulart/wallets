<!-- resources/views/wallet/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Minha Carteira') }}
        </h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <!-- Mensagens de sucesso/erro -->
        @if(session('success'))
            <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
                <ul class="mb-0 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulário de Depósito -->
        <form action="{{ route('wallet.deposit') }}" method="POST" class="mb-6 bg-white p-6 rounded shadow">
            @csrf
            <div class="mb-3">
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Valor do Depósito:</label>
                <input type="number" name="amount" id="amount" step="0.01" required
                       class="block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('amount') }}">
            </div>
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition shadow-md">
                Depositar
            </button>
        </form>

        <!-- Lista de Transações -->
        <h2 class="mb-3 text-xl font-semibold text-gray-800">Transações</h2>
        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b text-left text-gray-600">ID</th>
                        <th class="px-4 py-2 border-b text-left text-gray-600">Tipo</th>
                        <th class="px-4 py-2 border-b text-left text-gray-600">Valor</th>
                        <th class="px-4 py-2 border-b text-left text-gray-600">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)

                        <tr class="hover:bg-gray-50 {{ ($transaction->direction == 'Recebido' || $transaction->direction == 'Depósito') ? 'bg-green-100' : ($transaction->direction == 'Enviado' ? 'bg-red-100' : '') }}">
                            <td class="px-4 py-2 border-b">{{ $transaction->id }}</td>
                            <td class="px-4 py-2 border-b">{{ $transaction->direction }}</td>
                            <td class="px-4 py-2 border-b">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                            <td class="px-4 py-2 border-b">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-center text-gray-500">Nenhuma transação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Paginação -->
            <div class="mt-4 mb-4 mx-2">
                {{ $transactions->links() }}
            </div>
            <!-- Saldo -->
            <div class="mb-4 p-4 rounded bg-gray-100 text-gray-800">
                <p><strong>Saldo atual:</strong> R$ {{ number_format($balance, 2, ',', '.') }}</p>
                <p><strong>Saldo consolidado (entradas - saídas):</strong> R$ {{ number_format($totalBalance, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
