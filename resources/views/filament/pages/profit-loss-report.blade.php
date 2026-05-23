<x-filament-panels::page>
    @vite(['resources/css/app.css'])
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-4 border border-gray-200 dark:border-gray-800">
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-grow">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Dari Tanggal</label>
                    <input type="date" wire:model.live="fromDate" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>
                <div class="flex-grow">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Sampai Tanggal</label>
                    <input type="date" wire:model.live="toDate" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>
                <button wire:click="$refresh" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">Tampilkan</button>
            </div>
        </div>

        <!-- Report Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Pendapatan Card -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800">
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-gray-800 dark:text-gray-100 uppercase text-xs tracking-wider">Pendapatan & Penjualan</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Total Penjualan (Bruto)</span>
                        <span class="font-bold text-gray-950 dark:text-white">Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-red-600">
                        <span class="text-xs">Pajak (PPN)</span>
                        <span class="font-medium text-sm">- Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-red-600">
                        <span class="text-xs">HPP (Harga Pokok Penjualan)</span>
                        <span class="font-medium text-sm">- Rp {{ number_format($cogs, 0, ',', '.') }}</span>
                    </div>
                    <div class="pt-4 border-t border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <span class="font-bold text-gray-800 dark:text-gray-100">Laba Kotor (Gross Profit)</span>
                        <span class="text-xl font-black text-primary-600">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Pengeluaran Card -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800">
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-gray-800 dark:text-gray-100 uppercase text-xs tracking-wider">Biaya Operasional</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Total Biaya & Pengeluaran</span>
                        <span class="font-bold text-red-600">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-10"></div> <!-- Spacer -->
                    <div class="pt-4 border-t border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <span class="font-bold text-gray-800 dark:text-gray-100">Total Pengeluaran</span>
                        <span class="text-xl font-bold text-red-600">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Final Result Card -->
            <div class="md:col-span-2 bg-primary-600 rounded-2xl p-8 text-white shadow-xl shadow-primary-500/20 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h2 class="text-lg font-medium opacity-80">Laba / Rugi Bersih (Net Profit)</h2>
                    <p class="text-sm opacity-60">Periode {{ $fromDate }} s/d {{ $toDate }}</p>
                </div>
                <div class="text-center md:text-right">
                    <h1 class="text-4xl md:text-5xl font-black">Rp {{ number_format($netProfit, 0, ',', '.') }}</h1>
                    <p class="mt-2 text-xs font-bold uppercase tracking-widest bg-white/20 inline-block px-3 py-1 rounded-full">
                        {{ $netProfit >= 0 ? 'Surplus (Untung)' : 'Defisit (Rugi)' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
