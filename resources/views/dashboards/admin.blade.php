<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("Selamat datang, Admin!") }}
                    <p class="mt-4">Dari sini Anda bisa mengelola:</p>
                    <ul class="list-disc list-inside">
                        <li>Data Pengguna</li>
                        <li>Data Obat</li>
                        <li>Laporan Penjualan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>