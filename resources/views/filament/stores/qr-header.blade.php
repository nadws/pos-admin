<div class="mb-6">
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex flex-col md:flex-row items-center gap-6">
            <div class="p-3 bg-white rounded-lg border shadow-sm">
                {!! QrCode::size(150)->generate(
                    json_encode([
                        'slug' => $record->slug,
                        'name' => $record->name,
                        'api_url' => config('app.url') . '/api',
                    ]),
                ) !!}
            </div>

            <div class="flex-1 text-center md:text-left">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Setup Perangkat Kasir</h2>
                <p class="text-sm text-gray-500 mb-4">Scan QR Code ini dari aplikasi mobile untuk menghubungkan device ke
                    cabang <strong>{{ $record->name }}</strong>.</p>

                <div
                    class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900 text-blue-600 dark:text-blue-400 text-xs font-medium border border-blue-200">
                    Store ID: {{ $record->slug }}
                </div>
            </div>
        </div>
    </div>
</div>
