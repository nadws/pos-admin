<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uwais POS - Solusi Kasir Digital Modern</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-blue {
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
        }

        @keyframes bounce-slow {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-bounce-slow {
            animation: bounce-slow 4s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-[#FCFDFF] text-[#1F2937] antialiased">

    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="flex items-center justify-between max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-[#1E40AF] rounded flex items-center justify-center">
                        <span class="text-white font-bold text-sm">U</span>
                    </div>
                    <span class="text-lg font-[800] tracking-tight text-[#111827]">UWAIS<span
                            class="text-blue-600">POS</span></span>
                </div>
                <div
                    class="hidden md:flex items-center gap-8 text-[11px] font-bold text-gray-500 uppercase tracking-[0.2em]">
                    <a href="#fitur" class="hover:text-blue-700 transition">Fitur</a>
                    <a href="#harga" class="hover:text-blue-700 transition">Harga</a>
                    <a href="#kontak"
                        class="hover:text-blue-700 transition text-blue-600 font-extrabold italic">Berlangganan</a>
                </div>
            </div>

            <div class="flex items-center gap-2 md:gap-4">
                <a href="{{ url('/admin/login') }}"
                    class="text-sm font-bold text-gray-600 hover:text-blue-700 px-4 py-2">Masuk</a>
                <a href="{{ url('/admin/register') }}"
                    class="bg-[#1E40AF] text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-lg shadow-blue-200 hover:bg-blue-800 transition">Mulai
                    Sekarang</a>
            </div>
        </div>
    </nav>

    <header class="max-w-7xl mx-auto px-6 pt-32 pb-20 flex flex-col md:flex-row items-center gap-16">
        <div class="flex-1 text-left">
            <div
                class="inline-block bg-blue-50 border border-blue-100 px-4 py-1.5 rounded-full mb-6 text-blue-700 font-black text-[10px] uppercase tracking-widest italic">
                Sistem Kasir Cloud Terpercaya
            </div>

            <h1 class="text-5xl md:text-[68px] font-[800] tracking-tighter leading-[1] mb-6 text-[#111827]">
                Investasi <span class="text-blue-600 uppercase">Terbaik</span> <br> untuk bisnis Anda.
            </h1>

            <p class="text-gray-500 text-lg md:text-xl max-w-xl leading-relaxed mb-10">
                Lupakan pencatatan manual. Uwais POS hadir dengan fitur manajemen stok dan laporan real-time untuk
                mempercepat pertumbuhan toko Anda.
            </p>

            <div class="flex flex-wrap gap-4 items-center">
                <a href="#harga"
                    class="bg-[#111827] text-white px-10 py-4 rounded-2xl font-bold hover:bg-gray-800 transition shadow-xl">
                    Cek Detail Harga
                </a>
                <a href="#kontak" class="flex items-center gap-2 text-blue-600 font-bold px-6 py-4">
                    <i class="fab fa-whatsapp text-2xl"></i> Konsultasi Gratis
                </a>
            </div>
        </div>

        <div class="flex-1 relative">
            <div
                class="relative mx-auto border-gray-900 bg-gray-900 border-[12px] rounded-[3rem] h-[550px] w-[270px] shadow-2xl overflow-hidden">
                <div class="h-full w-full bg-white flex items-center justify-center">
                    <img src="{{ asset('image/dashboard.jpeg') }}" alt="Dashboard" class="w-full h-full object-cover">
                </div>
            </div>
            <div
                class="absolute -bottom-6 -left-8 bg-white/90 backdrop-blur p-5 rounded-[2rem] shadow-2xl border border-blue-50 md:block hidden animate-bounce-slow">
                <div class="flex items-center gap-4 text-left">
                    <div
                        class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold italic">
                        POS</div>
                    <div>
                        <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest leading-none mb-1">
                            Status Lisensi</p>
                        <p class="text-lg font-black text-gray-800 italic">Aktif 365 Hari</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="max-w-7xl mx-auto px-6 py-12">
        <div
            class="grid md:grid-cols-3 gap-8 text-center bg-[#1E40AF] rounded-[3rem] p-12 text-white shadow-2xl shadow-blue-200">
            <div>
                <p class="text-4xl font-black mb-1">100%</p>
                <p class="text-blue-200 text-[10px] font-bold uppercase tracking-widest">Data Cloud Aman</p>
            </div>
            <div class="border-y md:border-y-0 md:border-x border-blue-400/30 py-6 md:py-0">
                <p class="text-4xl font-black mb-1">500+</p>
                <p class="text-blue-200 text-[10px] font-bold uppercase tracking-widest">UMKM Terdaftar</p>
            </div>
            <div>
                <p class="text-4xl font-black mb-1">15MB</p>
                <p class="text-blue-200 text-[10px] font-bold uppercase tracking-widest">Super Ringan</p>
            </div>
        </div>
    </section>

    <section id="harga" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h2 class="text-3xl md:text-5xl font-[800] tracking-tighter mb-4 text-[#111827]">Harga Transparan.</h2>
            <p class="text-gray-500 mb-16 max-w-md mx-auto">Tanpa biaya tambahan tersembunyi. Satu harga untuk semua
                fitur premium.</p>

            <div class="max-w-md mx-auto relative">
                <div class="absolute -inset-2 bg-blue-600 rounded-[3.5rem] blur opacity-10"></div>
                <div
                    class="relative bg-white border border-gray-100 rounded-[3rem] p-12 shadow-sm border-b-4 border-b-blue-600">
                    <span
                        class="bg-blue-600 text-white text-[10px] font-black px-4 py-1 rounded-full uppercase tracking-[0.2em]">Paket
                        Annual</span>
                    <div class="mt-8 mb-8">
                        <p class="text-sm font-bold text-gray-400">Hanya</p>
                        <div class="flex justify-center items-start gap-1">
                            <span class="text-2xl font-bold mt-2 text-gray-800">Rp</span>
                            <span class="text-7xl font-black text-[#111827]">500k</span>
                        </div>
                        <p class="text-gray-400 font-bold mt-2 italic text-sm">per tahun</p>
                    </div>

                    <div class="space-y-4 mb-10 text-left border-t border-gray-50 pt-8">
                        <div class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-600"></i> <span
                                class="text-sm font-semibold">Transaksi Unlimited</span></div>
                        <div class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-600"></i> <span
                                class="text-sm font-semibold">Laporan Omzet & Laba</span></div>
                        <div class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-600"></i> <span
                                class="text-sm font-semibold">Cetak Struk Bluetooth</span></div>
                        <div class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-600"></i> <span
                                class="text-sm font-semibold">Akses Multi-Device</span></div>
                    </div>

                    <a href="#kontak"
                        class="block w-full bg-[#111827] text-white py-4 rounded-2xl font-bold hover:bg-gray-800 transition shadow-lg">
                        Berlangganan Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="kontak" class="py-24 max-w-7xl mx-auto px-6">
        <div
            class="bg-[#1E40AF] rounded-[3.5rem] p-10 md:p-20 text-white flex flex-col md:flex-row items-center gap-12 relative overflow-hidden">
            <div class="flex-1 z-10 text-left">
                <h2 class="text-4xl font-[800] tracking-tighter mb-6 leading-tight">Hubungi Kami untuk <br> Aktivasi
                    Akun.</h2>
                <p class="text-blue-100 text-lg mb-8 max-w-sm">
                    Kirim bukti pembayaran atau tanya seputar fitur melalui WhatsApp. Admin kami akan segera membantu
                    Anda.
                </p>
                <a href="https://wa.me/6285821875178?text=Halo%20Admin%20Uwais%20POS,%20saya%20tertarik%20berlangganan%20paket%20300rb/tahun"
                    target="_blank"
                    class="inline-flex items-center gap-4 bg-white text-blue-700 px-8 py-4 rounded-2xl font-bold shadow-2xl hover:bg-gray-50 transition active:scale-95 transition-all">
                    <i class="fab fa-whatsapp text-2xl"></i>
                    Chat WhatsApp Admin
                </a>
            </div>
            <div class="flex-1 z-10 text-right">
                <div class="inline-block bg-blue-500/20 p-8 rounded-[3rem] border border-white/10 backdrop-blur-sm">
                    <p class="text-xs uppercase tracking-[0.2em] font-bold text-blue-200 mb-2">Metode Pembayaran</p>
                    <div class="flex gap-6 opacity-80 grayscale invert">
                        <span class="text-xl font-black italic">BANK</span>
                        <span class="text-xl font-black italic">QRIS</span>
                        <span class="text-xl font-black italic">E-WALLET</span>
                    </div>
                </div>
            </div>
            <div class="absolute top-0 right-0 w-80 h-80 bg-blue-500 rounded-full -mr-40 -mt-40 opacity-20"></div>
        </div>
    </section>

    <footer class="bg-white py-12 border-t border-gray-100">
        <div
            class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center text-gray-400 text-xs font-bold uppercase tracking-widest">
            <p>© 2026 Uwais POS. All Rights Reserved.</p>
            <div class="flex gap-8 mt-4 md:mt-0">
                <a href="#" class="hover:text-blue-600">Privacy</a>
                <a href="#" class="hover:text-blue-600">Terms</a>
                <a href="#" class="hover:text-blue-600">Support</a>
            </div>
        </div>
    </footer>

</body>

</html>
