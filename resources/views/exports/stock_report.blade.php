<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Nama Barang</th>
            <th>Kategori Barang</th>
            <th>Tanggal Masuk</th>
            <th>Jumlah</th>
            <th>Lokasi Rak</th>
            <th>Nama Produsen</th>
            <th>Metode Bayar</th>
            <th>Pembayaran Transaksi</th>
            <th>Nota Transaksi</th>
            <th>Status Barang</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($incomingItems as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->kategori_barang }}</td>
                <td>{{ $item->tanggal_masuk_barang->format('d M Y') }}</td>
                <td>{{ $item->jumlah_barang }}</td>
                <td>{{ $item->lokasi_rak_barang ?? '-' }}</td>
                <td>{{ $item->nama_produsen ?? '-' }}</td>
                <td>{{ $item->metode_bayar ?? '-' }}</td>
                <td>{{ (float) preg_replace('/[^\d.]/', '', $item->pembayaran_transaksi) }}</td>
                <td>{{ $item->nota_transaksi ?? '-' }}</td>
                <td>
                    @php
                        $status = '';
                        if ($item->jumlah_barang > 50) {
                            $status = 'Banyak';
                        } elseif ($item->jumlah_barang > 0 && $item->jumlah_barang <= 50) {
                            $status = 'Sedikit';
                        } else {
                            $status = 'Habis';
                        }
                    @endphp
                    {{ $status }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table> 