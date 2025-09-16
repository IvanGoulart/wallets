<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transferência') }}
        </h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-4">Relatório Detalhado de Transações</h1>

        @if($transactions->isEmpty())
            <div class="p-4 bg-yellow-100 text-yellow-800 rounded">
                Nenhuma transação encontrada.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b">Data</th>
                            <th class="px-4 py-2 border-b">Tipo</th>
                            <th class="px-4 py-2 border-b">Valor</th>
                            <th class="px-4 py-2 border-b">De/Para</th>
                            <th class="px-4 py-2 border-b">Descrição</th>
                            <th class="px-4 py-2 border-b">Status</th>
                            <th class="px-4 py-2 border-b">Saldo após</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $t->formatted_date }}</td>
                                <td class="px-4 py-2 border-b">{{ $t->direction }}</td>
                                <td class="px-4 py-2 border-b">R$ {{ $t->formatted_amount }}</td>
                                <td class="px-4 py-2 border-b">
                                    {{ $t->counterparty ?? '-' }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $t->description ?? '-' }}
                                </td>
                                <td class="px-4 py-2 border-b">{{ ucfirst($t->status) }}</td>
                                <td class="px-4 py-2 border-b">R$ {{ $t->balance_after }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Paginação --}}
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
