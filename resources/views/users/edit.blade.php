<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('users.update', $user->idUser) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="namaUser" :value="__('Nama Lengkap')" />
                            <x-text-input id="namaUser" class="block mt-1 w-full" type="text" name="namaUser"
                                :value="old('namaUser', $user->namaUser)" required autofocus />
                            <x-input-error :messages="$errors->get('namaUser')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="username" :value="__('Username')" />
                            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username"
                                :value="old('username', $user->username)" required />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="level" :value="__('Level')" />
                            <select name="level" id="level"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="admin" {{ old('level', $user->level) == 'admin' ? 'selected' : '' }}>Admin
                                </option>
                                <option value="apoteker" {{ old('level', $user->level) == 'apoteker' ? 'selected' : '' }}>
                                    Apoteker
                                </option>
                                <option value="kasir" {{ old('level', $user->level) == 'kasir' ? 'selected' : '' }}>Kasir
                                </option>
                            </select>
                            <x-input-error :messages="$errors->get('level')" class="mt-2" />
                        </div>

                        <hr class="my-6">
                        <p class="text-sm text-gray-600 mb-4">Kosongkan password jika tidak ingin mengubahnya.</p>
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password Baru')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                                autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                                name="password_confirmation"/>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('users.index') }}"
                                class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Update') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>