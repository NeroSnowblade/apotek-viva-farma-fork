<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Obat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Notifikasi Sukses --}}
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <strong class="font-bold">Sukses!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Tombol Tambah Obat --}}
                    @if(Auth::user()->level == 'apoteker' || Auth::user()->level == 'admin')
                        <a href="{{ route('obat.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mb-4">
                            Tambah Obat Baru
                        </a>
                    @endif

                    {{-- Tabel Data Obat --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="ltr:text-left rtl:text-right">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Nama Obat</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Jenis</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Kategori</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Harga</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Stok</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Keterangan</th>
                                    @if(Auth::user()->level == 'apoteker')
                                        <th class="px-4 py-2">Aksi</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                @forelse ($obats as $obat)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                            {{ $obat->namaObat }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $obat->jenisObat }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $obat->kategori ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-2 text-gray-700">Rp
                                            {{ number_format($obat->harga, 0, ',', '.') }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $obat->stok }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $obat->keterangan ?? '-' }}
                                        </td>
                                        @if(Auth::user()->level == 'apoteker')
                                            <td class="whitespace-nowrap px-4 py-2">
                                                <a href="{{ route('obat.edit', $obat->idObat) }}"
                                                    class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">Edit</a>
                                                <form action="{{ route('obat.destroy', $obat->idObat) }}" method="POST"
                                                    class="inline-block"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">Hapus</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-500 py-4">
                                            Tidak ada data obat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Link Paginasi --}}
                    <div class="mt-4">
                        {{ $obats->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>