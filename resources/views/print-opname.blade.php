<body onload="window.print()">
    <h2>Form Stock Opname - {{ $opname->reference_number }}</h2>
    <p>Tanggal: {{ $opname->date }}</p>
    <table border="1" width="100%" cellpadding="10">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Stok Sistem</th>
                <th>Stok Fisik (Isi Disini)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($opname->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->system_stock }}</td>
                    <td>________________</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
