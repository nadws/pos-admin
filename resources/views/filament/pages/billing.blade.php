<x-filament-panels::page>
    @php
        $tenant = \Filament\Facades\Filament::getTenant();

        // 1. Perbaikan: Gunakan floor() agar angka 44.011... jadi 44 (bulat)
        $daysSinceJoined = floor($tenant->created_at->diffInDays(now()));

        // 2. Cek apakah sudah lewat masa aktif (Expired)
        $isSubscriptionExpired = $tenant->subscription_until && now()->gt($tenant->subscription_until);

        // 3. Cek apakah masa percobaan 30 hari sudah habis (Belum Bayar & > 30 hari)
        $isTrialExpired = !$tenant->is_active && $daysSinceJoined > 30;

        // 4. Tentukan apakah akses dikunci
        $isLocked = $isTrialExpired || $isSubscriptionExpired;

        // 5. Status Aktif Sejati (Lunas & Belum Expired)
        $isActive = $tenant->is_active && !$isSubscriptionExpired;

        // 6. Hitung persentase untuk progress bar (Maksimal 100%)
        $trialPercentage = min(($daysSinceJoined / 30) * 100, 100);
    @endphp

    <div class="space-y-6">
        {{-- 1. ALERT BOX: Jika Akses Dikunci --}}
        @if ($isLocked)
            <div class="p-4 bg-red-100 border border-red-200 rounded-xl flex items-center gap-4 shadow-sm animate-pulse">
                <div class="bg-red-500 p-2 rounded-lg">
                    <x-heroicon-o-lock-closed class="w-8 h-8 text-white" />
                </div>
                <div>
                    <h3 class="text-red-800 font-bold text-lg">Akses Toko Terkunci!</h3>
                    <p class="text-red-700 text-sm">
                        @if ($isTrialExpired)
                            Masa percobaan gratis 30 hari Anda telah berakhir (Hari ke-{{ $daysSinceJoined }}).
                        @else
                            Masa aktif langganan tahunan Anda telah habis pada
                            {{ $tenant->subscription_until?->format('d M Y') }}.
                        @endif
                        Silakan lakukan pembayaran untuk membuka kembali akses fitur kasir.
                    </p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- 2. SEKSI STATUS LAYANAN --}}
            <x-filament::section>
                <x-slot name="heading">Informasi Akun & Layanan</x-slot>

                <div class="space-y-4">
                    {{-- Badge Status --}}
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Status Pembayaran:</span>
                        @if ($tenant->is_active)
                            <x-filament::badge color="success">Sudah Lunas</x-filament::badge>
                        @else
                            <x-filament::badge color="warning">Masa Trial (Percobaan)</x-filament::badge>
                        @endif
                    </div>

                    {{-- Info Masa Aktif --}}
                    <div class="flex justify-between items-center py-2 border-b text-sm">
                        <span class="text-gray-600">Masa Aktif Hingga:</span>
                        <span class="font-bold {{ $isSubscriptionExpired ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $tenant->subscription_until?->format('d M Y') ?? 'Belum Diatur' }}
                        </span>
                    </div>

                    {{-- Info Masa Percobaan (Jika belum lunas) --}}
                    @if (!$tenant->is_active)
                        <div class="flex flex-col py-2 border-b text-sm space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Penggunaan Trial:</span>
                                <span class="font-bold {{ $daysSinceJoined > 30 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $daysSinceJoined }} / 30 Hari
                                </span>
                            </div>
                            {{-- Visual Progress Bar --}}
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $daysSinceJoined > 30 ? 'bg-red-500' : 'bg-green-500' }}"
                                    style="width: {{ $trialPercentage }}%"></div>
                            </div>
                        </div>
                    @endif

                    <div class="pt-2 text-center">
                        <p class="text-2xl font-extrabold text-primary-600">Rp 500.000 <span
                                class="text-xs text-gray-400 font-normal">/ Tahun</span></p>
                    </div>
                </div>
            </x-filament::section>

            {{-- 3. SEKSI METODE PEMBAYARAN --}}
            @if (!$isActive)
                <x-filament::section>
                    <x-slot name="heading">Instruksi Pembayaran</x-slot>

                    <div class="space-y-4">
                        <p class="text-sm text-gray-500 italic text-center text-balance">Transfer tepat <b>Rp
                                500.000</b> ke rekening di bawah ini:</p>

                        <div
                            class="p-4 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 relative overflow-hidden">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Bank
                                    Mandiri</span>
                                <x-heroicon-m-credit-card class="w-5 h-5 text-gray-400" />
                            </div>
                            <p class="text-2xl font-mono font-bold text-gray-800 tracking-tighter">310016510946</p>
                            <p class="text-sm text-gray-600">an. <span
                                    class="font-semibold uppercase text-gray-900">Nanda Wahyudi</span></p>
                        </div>

                        <x-filament::button
                            href="https://wa.me/6285751609104?text=Halo%20Admin%2C%20saya%20ingin%20konfirmasi%20pembayaran%20UwaisPOS%20untuk%20toko%3A%20{{ urlencode($tenant->name) }}"
                            tag="a" target="_blank" color="success" icon="heroicon-o-chat-bubble-left-right"
                            size="xl" class="w-full shadow-md">
                            Kirim Bukti Transfer (WhatsApp)
                        </x-filament::button>

                        <p class="text-[10px] text-gray-400 text-center uppercase font-bold tracking-widest">Aktivasi
                            diproses manual oleh admin 1x24 jam</p>
                    </div>
                </x-filament::section>
            @else
                {{-- Tampilan saat sudah Aktif --}}
                <x-filament::section>
                    <div class="text-center py-8 space-y-4">
                        <div class="flex justify-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <x-heroicon-o-sparkles class="w-12 h-12 text-green-600" />
                            </div>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">Selamat Berjualan!</h2>
                        <p class="text-sm text-gray-500 max-w-xs mx-auto">
                            Akun Anda terverifikasi lunas. Seluruh fitur kasir dan manajemen stok telah aktif
                            sepenuhnya.
                        </p>
                    </div>
                </x-filament::section>
            @endif
        </div>
    </div>
</x-filament-panels::page>
