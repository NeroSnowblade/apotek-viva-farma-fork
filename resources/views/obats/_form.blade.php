{{-- resources/views/obats/_form.blade.php --}}

{{-- Tentukan variabel untuk mode create/edit --}}
@php
    $isEdit = isset($obat);
@endphp

<form method="POST" action="{{ $isEdit ? route('obat.update', $obat->idObat) : route('obat.store') }}">
    @csrf
    {{-- Jika mode edit, tambahkan method PUT --}}
    @if($isEdit)
        @method('PUT')
    @endif

    <div>
        <x-input-label for="namaObat" :value="__('Nama Obat')" />
        {{-- Gunakan operator null coalescing (??) yang lebih singkat dari ternary --}}
        <x-text-input id="namaObat" class="block mt-1 w-full" type="text" name="namaObat" :value="old('namaObat', $obat->namaObat ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('namaObat')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="jenisObat" :value="__('Jenis Obat')" />
        <x-text-input id="jenisObat" class="block mt-1 w-full" type="text" name="jenisObat" :value="old('jenisObat', $obat->jenisObat ?? '')" required />
        <x-input-error :messages="$errors->get('jenisObat')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="kategori" :value="__('Kategori')" />
        <x-text-input id="kategori" class="block mt-1 w-full" type="text" name="kategori" :value="old('kategori', $obat->kategori ?? '')" />
        <x-input-error :messages="$errors->get('kategori')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="harga" :value="__('Harga')" />
        <x-text-input id="harga" class="block mt-1 w-full" type="number" name="harga" :value="old('harga', $obat->harga ?? '')" required />
        <x-input-error :messages="$errors->get('harga')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="stok" :value="__('Stok')" />
        <x-text-input id="stok" class="block mt-1 w-full" type="number" name="stok" :value="old('stok', $obat->stok ?? '')" required />
        <x-input-error :messages="$errors->get('stok')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="keterangan" :value="__('Keterangan')" />
        <textarea id="keterangan" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="keterangan">{{ old('keterangan', $obat->keterangan ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
    </div>

    <div class="flex items-center justify-end mt-4">
        <a href="{{ route('obat.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ __('Batal') }}
        </a>
        <x-primary-button class="ms-4">
            {{-- Teks tombol kondisional menggunakan ternary --}}
            {{ $isEdit ? __('Update') : __('Simpan') }}
        </x-primary-button>
    </div>
</form>