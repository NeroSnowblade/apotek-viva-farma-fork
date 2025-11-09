<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Transaksi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(Auth::user()->level == 'kasir' || Auth::user()->level == 'admin')
                        <a href="{{ route('transaksi.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mb-4">
                            Tambah Transaksi Baru
                        </a>
                    @endif
                    {{-- FORM FILTER --}}
                    <form method="GET" action="{{ route('transaksi.index') }}"
                        class="mb-6 flex flex-wrap items-end gap-4">

                        {{-- Filter Harian --}}
                        <div>
                            <label for="tanggal" class="block text-sm font-medium text-gray-700">Filter Harian</label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ request('tanggal') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        {{-- Filter Bulanan --}}
                        <div>
                            <label for="bulan" class="block text-sm font-medium text-gray-700">Filter Bulanan</label>
                            <input type="month" name="bulan" id="bulan" value="{{ request('bulan') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        {{-- Tombol --}}
                        <div class="flex gap-2">
                            <x-primary-button type="submit">
                                Filter
                            </x-primary-button>

                            {{-- Tombol Reset --}}
                            <a href="{{ route('transaksi.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Reset
                            </a>
                        </div>
                    </form>
                    {{-- Tabel Data Transaksi --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="ltr:text-left rtl:text-right">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">ID Transaksi</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Tanggal</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Nama Kasir</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Total Harga</th>
                                    <th class="px-4 py-2">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                @forelse ($transaksis as $transaksi)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                            #{{ $transaksi->idTransaksi }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-gray-700">
                                            {{ \Carbon\Carbon::parse($transaksi->tanggalTransaksi)->format('d M Y, H:i') }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-2 text-gray-700">
                                            {{ $transaksi->user->namaUser }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-2 text-gray-700">Rp
                                            {{ number_format($transaksi->totalHarga, 0, ',', '.') }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-2">
                                            <a href="{{ route('transaksi.invoice', $transaksi->idTransaksi) }}"
                                                class="inline-block rounded bg-blue-600 px-4 py-2 text-xs font-medium text-white hover:bg-blue-700">
                                                Lihat Invoice
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-500 py-4">
                                            Belum ada riwayat transaksi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Link Paginasi --}}
                    <div class="mt-4">
                        {{ $transaksis->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>