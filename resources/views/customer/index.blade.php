<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Belanja Obat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" x-data="transaction()">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Obat</h3>
                            <input type="text" x-model="search" placeholder="Cari nama obat..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-md mb-4">
                            <div class="max-h-96 overflow-y-auto border rounded-md">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="obat in filteredObats" :key="obat.idObat">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900"
                                                        x-text="obat.namaObat"></div>
                                                    <div class="text-sm text-gray-500"
                                                        x-text="`Stok: ${obat.stok}`"></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                    <button @click.prevent="addToCart(obat)" type="button"
                                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                                                        + Tambah
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="filteredObats.length === 0">
                                            <tr>
                                                <td colspan="2" class="text-center text-gray-500 py-4">
                                                    Obat tidak ditemukan.
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Keranjang</h3>
                            <div class="border rounded-md p-4">
                                <template x-if="cart.length === 0">
                                    <p class="text-gray-500 text-center">Keranjang masih kosong.</p>
                                </template>
                                <table class="min-w-full divide-y divide-gray-200" x-show="cart.length > 0">
                                    <tbody>
                                        <template x-for="(item, index) in cart" :key="item.idObat">
                                            <tr>
                                                <td class="py-2">
                                                    <p class="font-medium" x-text="item.namaObat"></p>
                                                    <p class="text-sm text-gray-500"
                                                        x-text="formatCurrency(item.harga)"></p>
                                                </td>
                                                <td class="py-2 text-center w-32">
                                                    <input type="number" x-model.number="item.jumlah"
                                                        @change="updateSubtotal(index)"
                                                        class="w-16 text-center border-gray-300 rounded-md" min="1"
                                                        :max="item.stok_awal">
                                                </td>
                                                <td class="py-2 text-right font-medium"
                                                    x-text="formatCurrency(item.subtotal)"></td>
                                                <td class="py-2 text-right">
                                                    <button @click.prevent="removeFromCart(index)"
                                                        class="text-red-600 hover:text-red-900">&times;</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <hr class="my-4" x-show="cart.length > 0">
                                <div class="flex justify-between items-center font-bold text-lg"
                                    x-show="cart.length > 0">
                                    <span>Total</span>
                                    <span x-text="formatCurrency(grandTotal)"></span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function transaction() {
            return {
                search: '',
                obats: @json($obats),
                cart: [],

                get filteredObats() {
                    if (this.search === '') {
                        return this.obats;
                    }
                    return this.obats.filter(obat =>
                        obat.namaObat.toLowerCase().includes(this.search.toLowerCase())
                    );
                },

                addToCart(obat) {
                    const existingItem = this.cart.find(item => item.idObat === obat.idObat);
                    if (existingItem) {
                        if (existingItem.jumlah < obat.stok) {
                            existingItem.jumlah++;
                            this.updateSubtotal(this.cart.indexOf(existingItem));
                        } else {
                            alert('Stok tidak mencukupi!');
                        }
                    } else {
                        this.cart.push({
                            idObat: obat.idObat,
                            namaObat: obat.namaObat,
                            harga: parseFloat(obat.harga),
                            stok_awal: obat.stok,
                            jumlah: 1,
                            subtotal: parseFloat(obat.harga)
                        });
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                updateSubtotal(index) {
                    let item = this.cart[index];
                    if (item.jumlah > item.stok_awal) {
                        item.jumlah = item.stok_awal;
                        alert('Jumlah melebihi stok yang tersedia!');
                    }
                    if (item.jumlah < 1) {
                        item.jumlah = 1;
                    }
                    item.subtotal = item.harga * item.jumlah;
                },

                get grandTotal() {
                    return this.cart.reduce((total, item) => total + item.subtotal, 0);
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
                }
            }
        }
    </script>
</x-app-layout>
