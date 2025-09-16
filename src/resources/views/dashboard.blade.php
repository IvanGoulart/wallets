<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                <!-- Carteira -->
                <a href="{{ route('wallet.index') }}"
                   class="flex items-center p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-10 w-10 text-blue-500"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 13h6v-2h-6v2z" />
                    </svg>
                    <span class="ml-4 text-lg font-medium text-gray-700">Minha Carteira</span>
                </a>

                <!-- Transferências -->
                <a href="{{ route('wallet.transfer.form') }}"
                   class="flex items-center p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-10 w-10 text-green-500"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v6h6M20 20v-6h-6M4 10l6-6m4 16l6-6" />
                    </svg>
                    <span class="ml-4 text-lg font-medium text-gray-700">Transferências</span>
                </a>

                <!-- Relatórios -->
                <a href="{{ route('wallet.detailed_report', ['relatorio' => 1]) }}"
                    class="flex items-center p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-10 w-10 text-yellow-500"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-6h4v6h5V9h-3L12 4 7 9H4v8z" />
                    </svg>
                    <span class="ml-4 text-lg font-medium text-gray-700">Relatório Consolidado</span>
                </a>

                <!-- Perfil -->
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-10 w-10 text-purple-500"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5.121 17.804A9 9 0 1118.364 4.56a9 9 0 01-13.243 13.243z" />
                    </svg>
                    <span class="ml-4 text-lg font-medium text-gray-700">Meu Perfil</span>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>
