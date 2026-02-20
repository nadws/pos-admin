<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uwais POS - Solusi Kasir Digital Modern</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Navigasi transparan saat scroll */
        .nav-blur {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Animasi mengapung untuk mockup HP */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        /* Gradient halus untuk background */
        .bg-soft {
            background: radial-gradient(circle at top right, #eff6ff, transparent),
                radial-gradient(circle at bottom left, #f8fafc, transparent);
        }
    </style>
</head>

<body class="bg-soft text-[#1F2937] antialiased">

    <nav class="fixed top-0 left-0 right-0 z-50 nav-blur border-b border-gray-100/50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div
                    class="w-10 h-10 bg-[#1E40AF] rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                    <span class="text-white font-bold text-xl">U</span>
                </div>
                <span class="text-xl font-[800] tracking-tight text-[#111827]">UWAIS<span
                        class="text-blue-600">POS</span></span>
            </div>

            <div
                class="hidden md:flex items-center gap-10 text-[11px] font-bold text-gray-400 uppercase tracking-[0.2em]">
                <a href="#fitur" class="hover:text-blue-700 transition-colors">Fitur</a>
                <a href="#harga" class="hover:text-blue-700 transition-colors">Harga</a>
                <a href="#kontak" class="text-blue-600 font-black italic">Berlangganan</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ url('/admin/login') }}"
                    class="text-sm font-bold text-gray-600 hover:text-blue-700 px-4 py-2">Masuk</a>
                <a href="{{ url('/admin/register') }}"
                    class="bg-[#1E40AF] text-white px-6 py-3 rounded-2xl text-sm font-bold shadow-xl shadow-blue-100 hover:bg-blue-800 hover:-translate-y-0.5 transition-all">Uji
                    Coba 1 Bulan Gratis</a>
            </div>
        </div>
    </nav>

    <header class="max-w-7xl mx-auto px-6 pt-32 lg:pt-48 pb-20">
        <div class="flex flex-col lg:flex-row items-center gap-16">
            <div class="flex-1 text-center lg:text-left order-2 lg:order-1">
                <div
                    class="inline-flex items-center gap-2 bg-blue-50 border border-blue-100 px-4 py-2 rounded-full mb-8">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                    </span>
                    <span class="text-blue-700 font-black text-[10px] uppercase tracking-widest italic">Sistem Kasir
                        Cloud Terpercaya</span>
                </div>

                <h1
                    class="text-4xl md:text-6xl lg:text-7xl font-[800] tracking-tighter leading-[1.1] mb-8 text-[#111827]">
                    Investasi <span class="text-blue-600 uppercase">Terbaik</span> <br class="hidden md:block"> untuk
                    bisnis Anda.
                </h1>

                <p class="text-gray-500 text-lg md:text-xl max-w-xl mx-auto lg:mx-0 leading-relaxed mb-10">
                    Lupakan pencatatan manual. Uwais POS hadir dengan fitur manajemen stok dan laporan real-time untuk
                    mempercepat pertumbuhan toko Anda.
                </p>

                <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                    <a href="#harga"
                        class="bg-[#111827] text-white px-10 py-5 rounded-[1.5rem] font-bold hover:bg-gray-800 transition-all shadow-2xl shadow-gray-200">
                        Cek Detail Harga
                    </a>
                    <a href="https://wa.me/6285821875178?text=Halo%20Admin%20Uwais%20POS,%20saya%20tertarik%20berlangganan%20paket%20500rb/tahun"
                        class="flex items-center justify-center gap-3 text-blue-600 font-bold px-8 py-5 bg-white rounded-[1.5rem] border border-gray-100 hover:bg-blue-50 transition-all">
                        <i class="fab fa-whatsapp text-2xl"></i> Konsultasi Gratis
                    </a>
                </div>
            </div>

            <div class="flex-1 relative flex justify-center order-1 lg:order-2">
                <div class="relative animate-float">
                    <div
                        class="relative mx-auto border-gray-900 bg-gray-900 border-[12px] rounded-[3.5rem] h-[500px] w-[250px] md:h-[600px] md:w-[300px] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.2)] overflow-hidden">
                        <img src="{{ asset('image/dashboard.jpeg') }}" alt="Dashboard"
                            class="w-full h-full object-cover">
                    </div>

                    <div
                        class="absolute -bottom-6 -left-8 md:-left-12 bg-white/95 backdrop-blur p-6 rounded-[2.5rem] shadow-2xl border border-blue-50 hidden sm:block">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-200">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div>
                                <p
                                    class="text-[10px] text-gray-400 font-black uppercase tracking-widest leading-none mb-1">
                                    Status Lisensi</p>
                                <p class="text-xl font-black text-gray-800 italic">Aktif 365 Hari</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="max-w-7xl mx-auto px-6 py-12">
        <div
            class="grid grid-cols-1 md:grid-cols-3 gap-8 bg-[#1E40AF] rounded-[3.5rem] p-12 text-white shadow-[0_30px_60px_-15px_rgba(30,64,175,0.4)]">
            <div class="text-center">
                <p class="text-5xl font-black mb-2 tracking-tight">100%</p>
                <p class="text-blue-200 text-[10px] font-extrabold uppercase tracking-[0.2em]">Data Cloud Aman</p>
            </div>
            <div class="text-center border-y md:border-y-0 md:border-x border-blue-400/30 py-8 md:py-0">
                <p class="text-5xl font-black mb-2 tracking-tight">500+</p>
                <p class="text-blue-200 text-[10px] font-extrabold uppercase tracking-[0.2em]">UMKM Terdaftar</p>
            </div>
            <div class="text-center">
                <p class="text-5xl font-black mb-2 tracking-tight">15MB</p>
                <p class="text-blue-200 text-[10px] font-extrabold uppercase tracking-[0.2em]">App Super Ringan</p>
            </div>
        </div>
    </section>

    <section id="harga" class="py-32">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h2 class="text-4xl md:text-5xl font-[900] tracking-tighter mb-4 text-[#111827]">Harga Transparan.</h2>
            <p class="text-gray-500 mb-16 max-w-sm mx-auto">Satu investasi untuk sukses jangka panjang bisnis Anda.</p>

            <div class="max-w-md mx-auto relative group">
                <div
                    class="absolute -inset-4 bg-blue-600 rounded-[4.5rem] blur opacity-10 group-hover:opacity-20 transition duration-500">
                </div>
                <div
                    class="relative bg-white border border-gray-100 rounded-[4rem] p-12 md:p-16 shadow-sm border-b-8 border-b-blue-600">
                    <span
                        class="bg-blue-600 text-white text-[10px] font-black px-6 py-2 rounded-full uppercase tracking-widest">Paling
                        Populer</span>

                    <div class="mt-12 mb-12">
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Hanya</p>
                        <div class="flex justify-center items-start gap-1">
                            <span class="text-3xl font-bold mt-3 text-gray-800 tracking-tight">Rp</span>
                            <span class="text-8xl font-black text-[#111827] tracking-tighter">500k</span>
                        </div>
                        <p class="text-gray-400 font-bold mt-2 italic">per tahun</p>
                    </div>

                    <div class="space-y-5 mb-12 text-left border-t border-gray-50 pt-10">
                        <div class="flex items-center gap-4 text-sm font-semibold text-gray-700">
                            <i class="fas fa-check-circle text-blue-600 text-xl"></i> Transaksi Unlimited
                        </div>
                        <div class="flex items-center gap-4 text-sm font-semibold text-gray-700">
                            <i class="fas fa-check-circle text-blue-600 text-xl"></i> Laporan Omzet & Laba
                        </div>
                        <div class="flex items-center gap-4 text-sm font-semibold text-gray-700">
                            <i class="fas fa-check-circle text-blue-600 text-xl"></i> Cetak Struk Bluetooth
                        </div>
                        <div class="flex items-center gap-4 text-sm font-semibold text-gray-700">
                            <i class="fas fa-check-circle text-blue-600 text-xl"></i> Akses Multi-Device
                        </div>
                    </div>

                    <a href="#kontak"
                        class="block w-full bg-[#111827] text-white py-5 rounded-[1.5rem] font-[800] hover:bg-gray-800 transition-all hover:scale-[1.02] shadow-xl">
                        Mulai Berlangganan
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="kontak" class="py-12 max-w-7xl mx-auto px-6 mb-24">
        <div
            class="bg-[#1E40AF] rounded-[4rem] p-10 md:p-24 text-white flex flex-col lg:flex-row items-center justify-between gap-16 relative overflow-hidden">
            <div class="z-10 text-center lg:text-left">
                <h2 class="text-4xl md:text-5xl font-[900] tracking-tighter mb-8 leading-tight">Siap Untuk <br
                        class="hidden lg:block"> Go Digital?</h2>
                <p class="text-blue-100 text-lg mb-10 max-w-sm mx-auto lg:mx-0">Konsultasi gratis atau kirim bukti
                    pembayaran melalui WhatsApp admin kami.</p>
                <a href="https://wa.me/6285821875178?text=Halo%20Admin%20Uwais%20POS,%20saya%20tertarik%20berlangganan%20paket%20500rb/tahun"
                    target="_blank"
                    class="inline-flex items-center gap-4 bg-white text-blue-700 px-10 py-5 rounded-[1.5rem] font-bold shadow-2xl hover:bg-gray-50 transition-all active:scale-95">
                    <i class="fab fa-whatsapp text-2xl"></i> Chat Admin Sekarang
                </a>
            </div>

            <div class="z-10 w-full lg:w-auto">
                <div class="bg-blue-500/20 p-10 rounded-[3rem] border border-white/10 backdrop-blur-sm">
                    <p
                        class="text-[10px] uppercase tracking-[0.3em] font-black text-blue-200 mb-6 text-center lg:text-left">
                        Dukungan Pembayaran</p>
                    <div
                        class="flex flex-wrap justify-center lg:justify-start gap-8 opacity-80  grayscale invert font-[900] italic text-xl">
                        <span class="text-white">BANK</span> <span class="text-white">QRIS</span>
                        <span class="text-white">E-WALLET</span>
                    </div>
                </div>
            </div>

            <div class="absolute top-0 right-0 w-80 h-80 bg-blue-500 rounded-full -mr-20 -mt-20 opacity-20 blur-3xl">
            </div>
        </div>
    </section>

    <footer class="bg-white py-16 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-sm">U</span>
                </div>
                <span class="font-bold tracking-tight text-gray-900">UWAIS POS.</span>
            </div>
            <p class="text-gray-400 text-[10px] font-extrabold uppercase tracking-[0.2em]">© 2026 Uwais POS. All Rights
                Reserved.</p>
            <div class="flex gap-8 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                <a href="#" class="hover:text-blue-600 transition">Privacy</a>
                <a href="#" class="hover:text-blue-600 transition">Terms</a>
                <a href="#" class="hover:text-blue-600 transition">Support</a>
            </div>
        </div>
    </footer>

</body>

</html>
