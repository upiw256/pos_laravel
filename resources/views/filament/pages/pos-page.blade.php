<x-filament-panels::page>
    @vite(['resources/css/app.css'])
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 h-full min-h-[85vh] -mt-4">

        <!-- ══════════════════════════════════════════════ -->
        <!-- Left: Product Browser (List Table)             -->
        <!-- ══════════════════════════════════════════════ -->
        <div class="md:col-span-8 flex flex-col gap-4 relative">

            <!-- Checkout Overlay -->
            @if($is_checkout_mode)
            <div class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm z-20 rounded-2xl flex flex-col items-center justify-center border border-white/20 dark:border-gray-800/50">
                <x-heroicon-o-lock-closed class="w-14 h-14 text-primary-500/80 mb-4" />
                <h3 class="text-xl font-black text-gray-800 dark:text-gray-100">Mode Pembayaran Aktif</h3>
                <p class="text-gray-500 mt-2 font-medium text-sm">Selesaikan atau batalkan pembayaran di panel kanan.</p>
                <button wire:click="cancelCheckout" class="mt-5 px-6 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-red-500 hover:text-red-500 text-gray-600 dark:text-gray-400 rounded-xl font-bold transition-all shadow-sm text-sm">
                    ← Batalkan &amp; Kembali
                </button>
            </div>
            @endif

            <!-- ── Search Bar + Category Filter ── -->
            <div class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-md sticky top-0 z-10 rounded-2xl shadow-sm p-4 border border-gray-100 dark:border-gray-800">
                <div class="flex flex-col lg:flex-row gap-3 items-center">

                    <!-- Search Input -->
                    <div class="relative w-full lg:w-96">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="h-4 w-4 text-primary-500" style="width:1rem;height:1rem;" />
                        </span>
                        <input
                            wire:model.live.debounce.250ms="search"
                            id="pos-search"
                            type="text"
                            autocomplete="off"
                            class="block w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all text-sm font-medium"
                            placeholder="Nama produk, SKU, atau scan barcode…"
                        >
                        @if($search)
                        <button wire:click="$set('search','')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                            <x-heroicon-o-x-circle class="h-4 w-4" style="width:1rem;height:1rem;" />
                        </button>
                        @endif
                    </div>

                    <!-- Category Pills -->
                    <div class="flex gap-2 overflow-x-auto flex-1 no-scrollbar">
                        <button
                            wire:click="selectCategory(null)"
                            class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all {{ is_null($selectedCategory) ? 'bg-primary-500 text-white shadow-md shadow-primary-500/30' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700' }}"
                        >Semua</button>
                        @foreach($this->categories as $category)
                            <button
                                wire:click="selectCategory({{ $category->id }})"
                                class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all {{ $selectedCategory == $category->id ? 'bg-primary-500 text-white shadow-md shadow-primary-500/30' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700' }}"
                            >{{ $category->name }}</button>
                        @endforeach
                    </div>
                </div>

                <!-- Result count hint -->
                <div class="mt-2 flex items-center justify-between px-1">
                    <span class="text-[11px] text-gray-400 font-medium">
                        {{ $this->products->count() }} produk ditemukan
                        @if($search) <span class="text-primary-500">untuk "<strong>{{ $search }}</strong>"</span> @endif
                    </span>
                    <span class="text-[11px] text-gray-400 font-medium hidden sm:block">
                        <x-heroicon-o-bolt class="inline h-3 w-3 text-yellow-500" style="width:0.75rem;height:0.75rem;" />
                        Full-Text Index Search
                    </span>
                </div>
            </div>

            <!-- ── Product List Table ── -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col" style="max-height: 72vh;">

                <!-- Table Header -->
                <div class="grid grid-cols-12 gap-2 px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-10">
                    <div class="col-span-5 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em]">Produk</div>
                    <div class="col-span-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em]">SKU</div>
                    <div class="col-span-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] text-center">Stok</div>
                    <div class="col-span-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] text-right">Harga</div>
                    <div class="col-span-1"></div>
                </div>

                <!-- Table Body -->
                <div class="overflow-y-auto custom-scrollbar flex-1">
                    @forelse($this->products as $product)

                        {{-- ══ Non-variant product: single row ══ --}}
                        @if(!$product->is_variant)
                        @php
                            $stock = $product->stocks->whereNull('variant_id')->first();
                            $stockQty = $stock?->quantity ?? 0;
                            $lowStock = $stock && $stockQty <= ($stock->min_stock ?? 5);
                        @endphp
                        <div
                            wire:click="addToCart({{ $product->id }})"
                            class="grid grid-cols-12 gap-2 px-4 py-3 border-b border-gray-50 dark:border-gray-800 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 cursor-pointer transition-colors group items-center"
                        >
                            <div class="col-span-5 flex items-center gap-3 min-w-0">
                                {{-- Tiny thumbnail or icon --}}
                                <div class="w-9 h-9 rounded-lg flex-shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    @if($product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <x-heroicon-o-cube class="h-4 w-4 text-gray-300 dark:text-gray-600" style="width:1rem;height:1rem;" />
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate leading-tight group-hover:text-primary-600 transition-colors">{{ $product->name }}</p>
                                    <p class="text-[10px] text-primary-500 font-bold uppercase tracking-wide truncate">{{ $product->category->name ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="col-span-2 min-w-0">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate block">{{ $product->sku ?? '—' }}</span>
                            </div>
                            <div class="col-span-2 text-center">
                                @if($stockQty <= 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">Habis</span>
                                @elseif($lowStock)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">{{ $stockQty }}</span>
                                @else
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $stockQty }}</span>
                                @endif
                            </div>
                            <div class="col-span-2 text-right">
                                <span class="text-sm font-black text-gray-900 dark:text-white whitespace-nowrap">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-span-1 flex justify-end">
                                <div class="w-7 h-7 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <x-heroicon-o-plus class="h-4 w-4" style="width:1rem;height:1rem;" />
                                </div>
                            </div>
                        </div>

                        {{-- ══ Variant product: expandable rows ══ --}}
                        @else
                        <div class="border-b border-gray-50 dark:border-gray-800">
                            {{-- Parent row (header) --}}
                            <div class="grid grid-cols-12 gap-2 px-4 py-2.5 bg-gray-50/70 dark:bg-gray-800/50 items-center">
                                <div class="col-span-5 flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-lg flex-shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/'.$product->image) }}" class="w-full h-full object-cover">
                                        @else
                                            <x-heroicon-o-squares-2x2 class="h-4 w-4 text-gray-300 dark:text-gray-600" style="width:1rem;height:1rem;" />
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate leading-tight">{{ $product->name }}</p>
                                        <p class="text-[10px] text-primary-500 font-bold uppercase tracking-wide">{{ $product->category->name ?? '—' }}</p>
                                    </div>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-xs text-gray-400 font-mono">{{ $product->sku ?? '—' }}</span>
                                </div>
                                <div class="col-span-2 text-center">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase px-2 py-0.5 bg-gray-200 dark:bg-gray-700 rounded-full">Varian</span>
                                </div>
                                <div class="col-span-2 text-right">
                                    <span class="text-xs text-gray-400">{{ number_format($product->sell_price, 0, ',', '.') }}+</span>
                                </div>
                                <div class="col-span-1"></div>
                            </div>

                            {{-- Variant sub-rows --}}
                            @foreach($product->variants as $variant)
                            @php
                                $varStock = $product->stocks->where('variant_id', $variant->id)->first();
                                $varQty   = $varStock?->quantity ?? 0;
                                $varLow   = $varStock && $varQty <= ($varStock->min_stock ?? 5);
                            @endphp
                            <div
                                wire:click.stop="addToCart({{ $product->id }}, {{ $variant->id }})"
                                class="grid grid-cols-12 gap-2 pl-16 pr-4 py-2.5 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 cursor-pointer transition-colors group items-center border-t border-gray-50 dark:border-gray-800/60"
                            >
                                <div class="col-span-5 flex items-center gap-2 min-w-0">
                                    <div class="w-1.5 h-1.5 rounded-full bg-primary-400 flex-shrink-0"></div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate group-hover:text-primary-600 transition-colors">{{ $variant->name }}</p>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-[11px] text-gray-400 font-mono truncate block">{{ $variant->sku ?? '—' }}</span>
                                </div>
                                <div class="col-span-2 text-center">
                                    @if($varQty <= 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">Habis</span>
                                    @elseif($varLow)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">{{ $varQty }}</span>
                                    @else
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $varQty }}</span>
                                    @endif
                                </div>
                                <div class="col-span-2 text-right">
                                    <span class="text-sm font-black text-gray-900 dark:text-white whitespace-nowrap">Rp {{ number_format($variant->sell_price, 0, ',', '.') }}</span>
                                </div>
                                <div class="col-span-1 flex justify-end">
                                    <div class="w-7 h-7 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <x-heroicon-o-plus class="h-4 w-4" style="width:1rem;height:1rem;" />
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                    @empty
                        <div class="py-24 text-center">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <x-heroicon-o-magnifying-glass class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                            </div>
                            <h3 class="text-base font-bold text-gray-400">Produk tidak ditemukan</h3>
                            <p class="text-gray-400 mt-1 text-xs max-w-xs mx-auto">Coba kata kunci lain atau pilih kategori berbeda</p>
                        </div>
                    @endforelse
                </div>
                <!-- /Table Body -->
            </div>
            <!-- /Product List Table -->
        </div>
        <!-- /Left Section -->

        <!-- ══════════════════════════════════════════════ -->
        <!-- Right: Cart & Summary (unchanged premium UI)   -->
        <!-- ══════════════════════════════════════════════ -->
        <div class="md:col-span-4 flex flex-col gap-6">
            <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 flex flex-col overflow-hidden h-full">

                <!-- Header Gradient -->
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
                        <div class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-colors border-b border-gray-50 dark:border-gray-800/50 last:border-none">
                            <div class="flex-grow min-w-0">
                                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100 leading-tight mb-1 truncate">{{ $item['name'] }}</h4>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-primary-500 bg-primary-50 dark:bg-primary-900/30 px-2 py-0.5 rounded-full">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                    <span class="text-[10px] text-gray-400 font-bold">×{{ $item['quantity'] }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
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
                        <div class="h-full flex flex-col items-center justify-center p-10 text-center">
                            <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-2xl flex items-center justify-center mb-4 rotate-12">
                                <x-heroicon-o-shopping-bag class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                            </div>
                            <h4 class="text-gray-400 font-bold">Keranjang Kosong</h4>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">Klik baris produk untuk menambahkan ke keranjang.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Summary & Payment -->
                <div class="p-6 bg-gray-50/80 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 space-y-4">

                    <!-- Customer -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Pelanggan</label>
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

                    <!-- Totals -->
                    <div class="space-y-2 border-b border-dashed border-gray-200 dark:border-gray-700 pb-4">
                        <div class="flex justify-between text-xs font-bold text-gray-500">
                            <span class="uppercase tracking-widest">Subtotal</span>
                            <span class="text-gray-900 dark:text-white font-black">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-gray-500">
                            <span class="uppercase tracking-widest">PPN {{ $tax_rate }}%</span>
                            <span class="text-gray-900 dark:text-white font-black">Rp {{ number_format($this->tax_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs font-bold text-gray-500">
                            <span class="uppercase tracking-widest">Diskon</span>
                            <div class="flex items-center gap-1.5 bg-white dark:bg-gray-900 px-2.5 py-1.5 rounded-lg shadow-sm">
                                <span class="text-primary-500 text-xs">Rp</span>
                                <input type="number" wire:model.live="discount_amount" class="w-20 text-right p-0 border-none bg-transparent font-black text-gray-900 dark:text-white focus:ring-0 text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Total Bayar</span>
                        <span class="text-2xl font-black text-primary-600 dark:text-primary-400 tracking-tighter">Rp {{ number_format($this->grand_total, 0, ',', '.') }}</span>
                    </div>

                    <!-- Payment Method Tabs -->
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['cash' => 'TUNAI', 'transfer' => 'BANK', 'qris' => 'QRIS'] as $key => $label)
                            <button
                                wire:click="$set('payment_method', '{{ $key }}')"
                                class="flex flex-col items-center py-3 rounded-2xl border-2 transition-all gap-1 {{ $payment_method == $key ? 'bg-primary-50 dark:bg-primary-900/30 border-primary-500 text-primary-600 shadow-md scale-105' : 'bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800 text-gray-400 hover:bg-gray-50' }}"
                            >
                                @if($key == 'cash') <x-heroicon-o-banknotes class="h-5 w-5" style="width:1.25rem;height:1.25rem;" />
                                @elseif($key == 'transfer') <x-heroicon-o-credit-card class="h-5 w-5" style="width:1.25rem;height:1.25rem;" />
                                @else <x-heroicon-o-qr-code class="h-5 w-5" style="width:1.25rem;height:1.25rem;" />
                                @endif
                                <span class="text-[9px] font-black tracking-widest">{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>

                    <!-- Payment Details (when checkout mode) -->
                    @if($is_checkout_mode)
                        <div class="bg-gray-100 dark:bg-gray-900 rounded-xl p-4 animate-item">
                            @if($payment_method === 'cash')
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest block">Uang Diterima (Rp)</label>
                                    <div class="grid grid-cols-3 gap-2 mb-3">
                                        <button wire:click="setCashPreset({{ $this->grand_total }})" class="py-1.5 px-2 bg-white dark:bg-gray-800 rounded-lg text-xs font-bold border border-gray-200 dark:border-gray-700 hover:border-primary-500 hover:text-primary-600 transition-colors">Pas</button>
                                        <button wire:click="setCashPreset(50000)"  class="py-1.5 px-2 bg-white dark:bg-gray-800 rounded-lg text-xs font-bold border border-gray-200 dark:border-gray-700 hover:border-primary-500 hover:text-primary-600 transition-colors">50rb</button>
                                        <button wire:click="setCashPreset(100000)" class="py-1.5 px-2 bg-white dark:bg-gray-800 rounded-lg text-xs font-bold border border-gray-200 dark:border-gray-700 hover:border-primary-500 hover:text-primary-600 transition-colors">100rb</button>
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
                                    <select wire:model="payment_detail" class="w-full bg-white dark:bg-gray-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary-500 shadow-sm">
                                        <option value="">-- Pilih Bank --</option>
                                        @foreach($bankOptions as $k => $bank)
                                            <option value="{{ $k }}">{{ $bank }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif($payment_method === 'qris')
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest block">Layanan E-Wallet / QRIS</label>
                                    <select wire:model="payment_detail" class="w-full bg-white dark:bg-gray-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary-500 shadow-sm">
                                        <option value="">-- Pilih Layanan --</option>
                                        @foreach($qrisOptions as $k => $qris)
                                            <option value="{{ $k }}">{{ $qris }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- CTA Buttons -->
                    @if(!$is_checkout_mode)
                        <button
                            wire:click="startCheckout"
                            @disabled(empty($cart))
                            class="w-full relative group overflow-hidden bg-primary-600 hover:bg-primary-700 active:scale-[0.98] disabled:bg-gray-300 dark:disabled:bg-gray-700 disabled:shadow-none text-white font-black py-4 rounded-2xl shadow-xl shadow-primary-500/40 transition-all flex items-center justify-center gap-3"
                        >
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                            <x-heroicon-o-arrow-right-circle class="h-6 w-6" style="width:1.5rem;height:1.5rem;" />
                            <span class="text-sm tracking-[0.1em]">LANJUT PEMBAYARAN</span>
                        </button>
                    @else
                        <div class="flex gap-2">
                            <button
                                wire:click="cancelCheckout"
                                class="w-1/3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-4 rounded-2xl transition-all text-sm"
                            >Batal</button>
                            <button
                                wire:click="submitCheckout"
                                @disabled($payment_method === 'cash' && !$this->is_cash_valid)
                                class="w-2/3 relative group overflow-hidden bg-green-600 hover:bg-green-700 active:scale-[0.98] disabled:bg-gray-300 dark:disabled:bg-gray-700 disabled:shadow-none text-white font-black py-4 rounded-2xl shadow-xl shadow-green-500/40 transition-all flex items-center justify-center gap-3"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/30 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                <x-heroicon-o-check-badge class="h-5 w-5" style="width:1.25rem;height:1.25rem;" />
                                <span class="text-sm tracking-[0.1em]">PROSES SEKARANG</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- /Right Section -->
    </div>

    <!-- ══ Receipt Modal ══ -->
    @if($showReceiptModal && $lastSale)
    <div class="fixed inset-0 z-[100] flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeReceipt"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-3xl w-[90%] shadow-2xl p-6 m-4 animate-item z-10 border border-white/10 dark:border-gray-800 mx-auto" style="max-width:450px!important;">
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
                    <span class="text-xl font-black text-primary-600">Rp {{ number_format($lastSale['grand_total'], 0, ',', '.') }}</span>
                </div>
                @if($lastSale['payment_method'] === 'cash')
                <div class="flex justify-between pt-1">
                    <span class="text-xs text-gray-500 font-medium">Tunai</span>
                    <span class="text-sm font-black text-gray-900 dark:text-gray-200">Rp {{ number_format($lastSale['cash_tendered'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-gray-500 font-medium">Kembalian</span>
                    <span class="text-sm font-black text-green-600">Rp {{ number_format($lastSale['change_amount'], 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
            <div class="flex gap-3">
                <button wire:click="closeReceipt" class="flex-1 py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl transition-all text-sm">
                    Tutup
                </button>
                <a href="{{ route('pos.receipt', $lastSale['id']) }}" target="_blank" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all flex items-center justify-center gap-2 text-sm">
                    <x-heroicon-o-printer class="w-4 h-4" />
                    Cetak Struk
                </a>
            </div>
        </div>
    </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
        .no-scrollbar::-webkit-scrollbar { display: none; }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-item { animation: slideIn 0.25s ease-out forwards; }
    </style>
</x-filament-panels::page>
