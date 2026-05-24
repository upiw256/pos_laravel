<x-filament-panels::page>
    @vite(['resources/css/app.css'])
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 h-full min-h-[85vh] -mt-4">
        
        <!-- Left Section: Product Browser -->
        <div class="md:col-span-8 flex flex-col gap-6 relative">
            <!-- Checkout Mode Overlay -->
            @if($is_checkout_mode)
            <div class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm z-20 rounded-3xl flex flex-col items-center justify-center border border-white/20 dark:border-gray-800/50">
                <x-heroicon-o-lock-closed class="w-16 h-16 text-primary-500/80 mb-4" />
                <h3 class="text-2xl font-black text-gray-800 dark:text-gray-100">Mode Pembayaran Aktif</h3>
                <p class="text-gray-500 mt-2 font-medium">Selesaikan atau batalkan pembayaran di jendela sebelah kanan.</p>
                <button wire:click="cancelCheckout" class="mt-6 px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-red-500 hover:text-red-500 text-gray-600 dark:text-gray-400 rounded-xl font-bold transition-all shadow-sm">
                    Batalkan & Kembali Belanja
                </button>
            </div>
            @endif

            <!-- Search & Categories (Glassmorphic) -->
            <div class="bg-white/70 dark:bg-gray-900/70 backdrop-blur-md sticky top-0 z-10 rounded-2xl shadow-sm p-5 border border-white/20 dark:border-gray-800/50">
                <div class="flex flex-col lg:flex-row gap-5 justify-between items-center">
                    <div class="relative w-full lg:w-1/2">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-primary-500" style="width:1.25rem;height:1.25rem;" />
                        </span>
                        <input 
                            wire:model.live.debounce.300ms="search"
                            type="text" 
                            class="block w-full pl-12 pr-4 py-3 bg-gray-50/50 dark:bg-gray-800/50 border-none rounded-xl focus:ring-2 focus:ring-primary-500 transition-all text-sm" 
                            placeholder="Cari produk atau scan barcode..."
                        >
                    </div>
                    
                    <div class="flex gap-2 overflow-x-auto w-full lg:w-1/2 no-scrollbar scroll-smooth">
                        <button 
                            wire:click="selectCategory(null)"
                            class="px-5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all {{ is_null($selectedCategory) ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                        >
                            Semua
                        </button>
                        @foreach($this->categories as $category)
                            <button 
                                wire:click="selectCategory({{ $category->id }})"
                                class="px-5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all {{ $selectedCategory == $category->id ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                            >
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 overflow-y-auto max-h-[70vh] pr-2 custom-scrollbar pb-10">
                @forelse($this->products as $product)
                    <div 
                        class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col group transition-all hover:shadow-xl hover:shadow-primary-500/5 hover:-translate-y-1 {{ !$product->is_variant ? 'cursor-pointer' : '' }}"
                        @if(!$product->is_variant) wire:click="addToCart({{ $product->id }})" @endif
                    >
                        <div class="relative aspect-square overflow-hidden bg-gray-50 dark:bg-gray-800">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-200 dark:text-gray-700">
                                    <x-heroicon-o-photo class="h-16 w-16" style="width:4rem;height:4rem;" />
                                </div>
                            @endif
                            
                            @if(!$product->is_variant)
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3 pointer-events-none">
                                    <span class="w-full bg-white text-primary-600 font-bold py-2 rounded-xl flex items-center justify-center gap-2 shadow-lg">
                                        <x-heroicon-o-plus-circle class="h-5 w-5" style="width:1.25rem;height:1.25rem;" />
                                        TAMBAH
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="p-4 flex-grow flex flex-col">
                            <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.2em] mb-1 italic">{{ $product->category->name ?? 'Misc' }}</span>
                            <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100 line-clamp-2 leading-tight flex-grow">{{ $product->name }}</h2>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-lg font-black text-gray-900 dark:text-white">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</span>
                                @if(!$product->is_variant)
                                    <span class="text-[10px] font-bold text-primary-500 bg-primary-50 dark:bg-primary-900/30 px-2 py-1 rounded-full flex items-center gap-1">
                                        <x-heroicon-o-plus class="h-3 w-3" style="width:0.75rem;height:0.75rem;" /> Tambah
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($product->is_variant)
                            <div class="px-4 py-3 bg-gray-50/50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Varian:</p>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($product->variants as $variant)
                                        <button 
                                            wire:click.stop="addToCart({{ $product->id }}, {{ $variant->id }})"
                                            class="text-[10px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg py-2 px-2 hover:border-primary-500 hover:bg-primary-50 hover:text-primary-600 font-bold transition-all truncate active:scale-95"
                                            title="{{ $variant->name }}"
                                        >
                                            {{ $variant->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full py-32 text-center rounded-3xl bg-gray-50/50 dark:bg-gray-800/20 border-2 border-dashed border-gray-200 dark:border-gray-800">
                        <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-heroicon-o-magnifying-glass class="h-12 w-12 text-gray-300 dark:text-gray-600" />
                        </div>
                        <h2 class="text-2xl font-bold text-gray-400">Oops! Produk Tidak Ada</h2>
                        <p class="text-gray-500 mt-2 max-w-xs mx-auto text-sm">Coba cari dengan kata kunci lain atau pilih kategori yang berbeda.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Section: Cart & Summary -->
        <div class="md:col-span-4 flex flex-col gap-6">
            <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 flex flex-col overflow-hidden h-full">
                <!-- Header (Premium Gradient) -->
                <div class="p-6 bg-gradient-to-br from-primary-600 to-indigo-700 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="absolute -left-4 -bottom-4 w-20 h-20 bg-primary-400/20 rounded-full blur-xl"></div>
                    
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="bg-white/20 backdrop-blur-md p-3 rounded-2xl text-white">
                            <x-heroicon-o-shopping-cart class="h-6 w-6" style="width:1.5rem;height:1.5rem;" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white tracking-tight">Checkout</h2>
                            <p class="text-[10px] text-primary-100 font-bold uppercase tracking-[0.2em] opacity-80">{{ now()->format('l, d F Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="flex-grow overflow-y-auto custom-scrollbar p-2">
                    @forelse($cart as $key => $item)
                        <div class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-colors border-b border-gray-50 dark:border-gray-800/50 last:border-none">
                            <div class="flex-grow">
                                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100 leading-tight mb-1">{{ $item['name'] }}</h4>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-primary-500 bg-primary-50 dark:bg-primary-900/30 px-2 py-0.5 rounded-full">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                    <span class="text-[10px] text-gray-400 font-bold">QTY: {{ $item['quantity'] }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="text-sm font-black text-gray-950 dark:text-white">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                <div class="flex gap-1">
                                    <button wire:click="updateQty('{{ $key }}', -1)" class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-primary-100 hover:text-primary-600 transition-colors">
                                        <x-heroicon-o-minus class="h-3 w-3" style="width:0.75rem;height:0.75rem;" />
                                    </button>
                                    <button wire:click="updateQty('{{ $key }}', 1)" class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-primary-100 hover:text-primary-600 transition-colors">
                                        <x-heroicon-o-plus class="h-3 w-3" style="width:0.75rem;height:0.75rem;" />
                                    </button>
                                    <button wire:click="removeItem('{{ $key }}')" class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 hover:bg-red-600 hover:text-white text-red-500 transition-all">
                                        <x-heroicon-o-trash class="h-3 w-3" style="width:0.75rem;height:0.75rem;" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center p-12 text-center">
                            <div class="w-20 h-20 bg-gray-50 dark:bg-gray-800 rounded-3xl flex items-center justify-center mb-6 rotate-12 group hover:rotate-0 transition-transform">
                                <x-heroicon-o-shopping-bag class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                            </div>
                            <h4 class="text-gray-400 font-bold text-lg">Keranjang Kosong</h4>
                            <p class="text-xs text-gray-500 mt-2 leading-relaxed">Ketuk produk untuk mulai memasukkan ke daftar belanja pelanggan.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Footer Calculations -->
                <div class="p-8 bg-gray-50/80 dark:bg-gray-800/50 backdrop-blur-sm border-t border-gray-100 dark:border-gray-800 space-y-5">
                    <!-- Customer Selection -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Data Pelanggan</label>
                        <div class="relative">
                            <select wire:model="customer_id" class="block w-full py-2.5 pl-10 pr-4 border-none bg-white dark:bg-gray-900 rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary-500 shadow-sm border border-gray-100 dark:border-gray-800">
                                <option value="">Walk-in Customer (Umum)</option>
                                @foreach($this->customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <x-heroicon-o-user class="absolute left-3 top-3 h-4 w-4 text-primary-500" style="width:1rem;height:1rem;" />
                        </div>
                    </div>

                    <div class="space-y-3 border-b border-dashed border-gray-200 dark:border-gray-700 pb-5">
                        <div class="flex justify-between text-xs font-bold text-gray-500">
                            <span class="uppercase tracking-widest">Subtotal</span>
                            <span class="text-gray-900 dark:text-white font-black">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-gray-500">
                            <span class="uppercase tracking-widest">Pajak (PPN {{ $tax_rate }}%)</span>
                            <span class="text-gray-900 dark:text-white font-black">Rp {{ number_format($this->tax_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs font-bold text-gray-500">
                            <span class="uppercase tracking-widest">Diskon Khusus</span>
                            <div class="flex items-center gap-2 bg-white dark:bg-gray-900 px-3 py-1.5 rounded-lg shadow-sm">
                                <span class="text-primary-500 text-xs">Rp</span>
                                <input type="number" wire:model.live="discount_amount" class="w-20 text-right p-0 border-none bg-transparent font-black text-gray-900 dark:text-white focus:ring-0 text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center py-2">
                        <span class="text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Total Bayar</span>
                        <span class="text-3xl font-black text-primary-600 dark:text-primary-400 tracking-tighter">Rp {{ number_format($this->grand_total, 0, ',', '.') }}</span>
                    </div>

                    <!-- Payment Method (Curated Tabs) -->
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['cash' => 'TUNAI', 'transfer' => 'BANK', 'qris' => 'QRIS'] as $key => $label)
                            <button 
                                wire:click="$set('payment_method', '{{ $key }}')" 
                                class="flex flex-col items-center py-3 rounded-2xl border-2 transition-all gap-1 {{ $payment_method == $key ? 'bg-primary-50 dark:bg-primary-900/30 border-primary-500 text-primary-600 shadow-md scale-105' : 'bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800 text-gray-400 hover:bg-gray-50' }}"
                            >
                                @if($key == 'cash') <x-heroicon-o-banknotes class="h-5 w-5" style="width:1.25rem;height:1.25rem;" /> @elseif($key == 'transfer') <x-heroicon-o-credit-card class="h-5 w-5" style="width:1.25rem;height:1.25rem;" /> @else <x-heroicon-o-qr-code class="h-5 w-5" style="width:1.25rem;height:1.25rem;" /> @endif
                                <span class="text-[9px] font-black tracking-widest">{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>

                    <!-- Payment Details Form -->
                    @if($is_checkout_mode)
                        <div class="bg-gray-100 dark:bg-gray-900 rounded-xl p-4 mt-2 animate-item">
                            @if($payment_method === 'cash')
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest block">Uang Diterima (Rp)</label>
                                    <div class="grid grid-cols-3 gap-2 mb-3">
                                        <button wire:click="setCashPreset({{ $this->grand_total }})" class="py-1.5 px-2 bg-white dark:bg-gray-800 rounded-lg text-xs font-bold border border-gray-200 dark:border-gray-700 hover:border-primary-500 hover:text-primary-600 transition-colors">Uang Pas</button>
                                        <button wire:click="setCashPreset(50000)" class="py-1.5 px-2 bg-white dark:bg-gray-800 rounded-lg text-xs font-bold border border-gray-200 dark:border-gray-700 hover:border-primary-500 hover:text-primary-600 transition-colors">50.000</button>
                                        <button wire:click="setCashPreset(100000)" class="py-1.5 px-2 bg-white dark:bg-gray-800 rounded-lg text-xs font-bold border border-gray-200 dark:border-gray-700 hover:border-primary-500 hover:text-primary-600 transition-colors">100.000</button>
                                    </div>
                                    <input type="number" wire:model.live="cash_tendered" class="w-full text-right bg-white dark:bg-gray-800 border-none rounded-xl font-black text-lg focus:ring-2 focus:ring-primary-500">
                                    
                                    <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <span class="text-xs font-bold text-gray-500">Kembalian</span>
                                        <span class="text-base font-black {{ $this->change_amount > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                                            Rp {{ number_format($this->change_amount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @elseif($payment_method === 'transfer')
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest block">Pilih Bank</label>
                                    <select wire:model="payment_detail" class="w-full bg-white dark:bg-gray-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary-500 shadow-sm border border-gray-100 dark:border-gray-800">
                                        <option value="">-- Pilih Bank --</option>
                                        @foreach($bankOptions as $key => $bank)
                                            <option value="{{ $key }}">{{ $bank }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif($payment_method === 'qris')
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest block">Layanan E-Wallet/QRIS</label>
                                    <select wire:model="payment_detail" class="w-full bg-white dark:bg-gray-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary-500 shadow-sm border border-gray-100 dark:border-gray-800">
                                        <option value="">-- Pilih Layanan --</option>
                                        @foreach($qrisOptions as $key => $qris)
                                            <option value="{{ $key }}">{{ $qris }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(!$is_checkout_mode)
                        <button 
                            wire:click="startCheckout"
                            @disabled(empty($cart))
                            class="w-full relative group overflow-hidden bg-primary-600 hover:bg-primary-700 active:scale-[0.98] disabled:bg-gray-300 dark:disabled:bg-gray-700 disabled:shadow-none text-white font-black py-5 rounded-2xl shadow-xl shadow-primary-500/40 transition-all flex items-center justify-center gap-4 mt-2"
                        >
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                            <x-heroicon-o-arrow-right-circle class="h-7 w-7" style="width:1.75rem;height:1.75rem;" />
                            <span class="text-sm tracking-[0.1em]">LANJUT PEMBAYARAN</span>
                        </button>
                    @else
                        <div class="flex gap-2 mt-2">
                            <button 
                                wire:click="cancelCheckout"
                                class="w-1/3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-5 rounded-2xl transition-all"
                            >
                                Batal
                            </button>
                            <button 
                                wire:click="submitCheckout"
                                @disabled($payment_method === 'cash' && !$this->is_cash_valid)
                                class="w-2/3 relative group overflow-hidden bg-green-600 hover:bg-green-700 active:scale-[0.98] disabled:bg-gray-300 dark:disabled:bg-gray-700 disabled:shadow-none text-white font-black py-5 rounded-2xl shadow-xl shadow-green-500/40 transition-all flex items-center justify-center gap-3"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/30 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                <x-heroicon-o-check-badge class="h-6 w-6" style="width:1.5rem;height:1.5rem;" />
                                <span class="text-sm tracking-[0.1em]">PROSES SEKARANG</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Receipt -->
    @if($showReceiptModal && $lastSale)
    <div class="fixed inset-0 z-[100] flex items-center justify-center">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeReceipt"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white dark:bg-gray-900 rounded-3xl w-[90%] shadow-2xl p-6 m-4 animate-item z-10 border border-white/10 dark:border-gray-800 mx-auto" style="max-width: 450px !important;">
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                    <x-heroicon-o-check-circle class="h-10 w-10 text-green-500" />
                </div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white">Transaksi Berhasil!</h3>
                <p class="text-sm text-gray-500 mt-1 font-bold">{{ $lastSale['reference_no'] }}</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-4 mb-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-xs text-gray-500 font-medium tracking-widest uppercase">Metode</span>
                    <span class="text-xs font-bold uppercase text-gray-900 dark:text-gray-100">{{ $lastSale['payment_method'] }} {{ $lastSale['payment_detail'] ? '- '.$lastSale['payment_detail'] : '' }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-y border-dashed border-gray-200 dark:border-gray-700">
                    <span class="text-xs text-gray-500 font-medium tracking-widest uppercase">Total</span>
                    <span class="text-xl font-black shrink-0 text-primary-600">Rp {{ number_format($lastSale['grand_total'], 0, ',', '.') }}</span>
                </div>
                @if($lastSale['payment_method'] === 'cash')
                <div class="flex justify-between pt-1">
                    <span class="text-xs text-gray-500 font-medium">Tunai</span>
                    <span class="text-sm font-black text-gray-900 dark:text-gray-200">Rp {{ number_format($lastSale['cash_tendered'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-gray-500 font-medium">Kembali</span>
                    <span class="text-sm font-black text-green-600">Rp {{ number_format($lastSale['change_amount'], 0, ',', '.') }}</span>
                </div>
                @endif
            </div>

            <div class="flex gap-3">
                <button wire:click="closeReceipt" class="flex-1 py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl transition-all">
                    Tutup
                </button>
                <a href="{{ route('pos.receipt', $lastSale['id']) }}" target="_blank" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all flex items-center justify-center gap-2">
                    <x-heroicon-o-printer class="w-5 h-5" />
                    Cetak Struk
                </a>
            </div>
            
        </div>
    </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.1); border-radius: 10px; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        
        /* Animation keyframes */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-item { animation: slideIn 0.3s ease-out forwards; }
    </style>
</x-filament-panels::page>
