<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Invoice: #' . $transaksi->idTransaksi) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Notifikasi Sukses --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Sukses!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">

                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-bold">Apotek Viva Farma</h3>
                            <p class="text-sm text-gray-600">Senayan Babakan, Sarinagen, Kec. Cipongkor, Kabupaten Bandung Barat, Jawa Barat 40564</p>
                        </div>
                        <div class="text-right">
                            <h3 class="text-lg font-bold">INVOICE</h3>
                            <p class="text-sm text-gray-600">ID Transaksi: #{{ $transaksi->idTransaksi }}</p>
                            <p class="text-sm text-gray-600">Tanggal: {{ \Carbon\Carbon::parse($transaksi->tanggalTransaksi)->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <p class="text-sm text-gray-600">Kasir:</p>
                        <p class="font-medium">{{ $transaksi->user->namaUser }}</p>
                    </div>

                    {{-- Daftar Item --}}
                    <div class="overflow-x-auto border rounded-md">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-left text-gray-900">Nama Obat</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Jumlah</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-right text-gray-900">Harga Satuan</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-right text-gray-900">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($transaksi->details as $item)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">{{ $item->nama_obat }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700 text-center">{{ $item->jumlah }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700 text-right">Rp {{ number_format($item->harga_saat_transaksi, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Total --}}
                    <div class="mt-6 flex justify-end">
                        <div class="w-full max-w-xs">
                            <div class="flex justify-between">
                                <span class="font-bold text-lg text-gray-900">Total</span>
                                <span class="font-bold text-lg text-gray-900">Rp {{ number_format($transaksi->totalHarga, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-600">Terima kasih atas kunjungan Anda.</p>
                        {{-- Tombol untuk kembali ke POS --}}
                        @if(auth()->check() && !in_array(auth()->user()->level, ['apoteker', 'customer']))
                            <a href="{{ route('transaksi.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Transaksi Baru
                            </a>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>