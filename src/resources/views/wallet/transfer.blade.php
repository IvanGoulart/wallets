<!-- resources/views/wallet/transfer.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transferência') }}
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

        <!-- Formulário de Transferência -->
        <form action="{{ route('wallet.transfer') }}" method="POST" class="mb-6 bg-white p-6 rounded shadow">
            @csrf
            <h2 class="mb-4 text-xl font-semibold text-gray-800">Transferência</h2>

            <!-- Seleção do destinatário -->
            <div class="mb-3">
                <label for="recipient_id" class="block text-sm font-medium text-gray-700 mb-1">Destinatário:</label>
                <select name="recipient_id" id="recipient_id" required
                        class="block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Selecione um usuário --</option>
                    @foreach($users as $user)
                        @if($user->id !== auth()->id()) <!-- evita auto-transferência -->
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Valor -->
            <div class="mb-3">
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Valor da Transferência:</label>
                <input type="number" name="amount" id="amount" step="0.01" required
                       class="block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('amount') }}">
            </div>

            <button type="submit"
                    class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition shadow-md">
                Transferir
            </button>
        </form>

        <!-- Lista de Transações -->
        <h2 class="mb-3 text-xl font-semibold text-gray-800">Histórico de Transações</h2>
        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b text-left text-gray-600">ID</th>
                        <th class="px-4 py-2 border-b text-left text-gray-600">Tipo</th>
                        <th class="px-4 py-2 border-b text-left text-gray-600">Valor</th>
                        <th class="px-4 py-2 border-b text-left text-gray-600">Descrição</th>
                        <th class="px-4 py-2 border-b text-left text-gray-600">Data</th>
                        <th class="px-4 py-2 border-b text-left text-gray-600">Reverter</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50 {{ ($transaction->direction == 'Recebido' || $transaction->direction == 'Depósito') ? 'bg-green-100' : ($transaction->direction == 'Enviado' ? 'bg-red-100' : '') }}">
                            <td class="px-4 py-2 border-b">{{ $transaction->id }}</td>
                            <td class="px-4 py-2 border-b">
                                @if($transaction->type === 'deposit')
                                    <span class="text-green-600 font-semibold">Recebido</span>
                                @elseif($transaction->type === 'transfer')
                                    <span class="text-red-600 font-semibold">Enviado</span>
                                @else
                                    {{ $transaction->type }}
                                @endif
                            </td>
                            <td class="px-4 py-2 border-b">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                            <td class="px-4 py-2 border-b">{{ $transaction->description }}</td>
                            <td class="px-4 py-2 border-b">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2 border-b">
                                @if($transaction->type === 'transfer' && $transaction->status !== 'reversed')

                                    <form action="{{ route('wallet.transaction.revert', $transaction) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="bg-yellow-400 text-white px-3 py-1 rounded hover:bg-yellow-500 transition">
                                            Desfazer
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center text-gray-500">Nenhuma transação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
