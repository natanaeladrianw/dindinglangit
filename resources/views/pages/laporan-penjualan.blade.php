@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Penjualan Menu</h3>
        {{-- Form Filter Tanggal --}}
        <form action="/laporan-penjualan" method="GET" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $startDate) }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Akhir:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $endDate) }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-dark w-100">Filter</button>
                </div>
            </div>
        </form>

        <table class="table table-hover" id="transaksi">
            <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Item</th>
                <th class="text-white text-center" scope="col">Total pesanan</th>
                <th class="text-white text-center" scope="col">Status</th>
                <th class="text-white text-center" scope="col">Total Pembayaran</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($transaksis->isEmpty())
                    <tr>
                        Tidak ada transaksi.
                    </tr>
                @else
                    @foreach ($transaksis as $transaksi)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $transaksi->menus->pluck('nama_item')->join(', ') }}</td>
                            <td class="text-center">
                                {{
                                    $transaksi->menus->sum(function ($menu) {
                                        return $menu->pivot->jumlah_pesanan;
                                    })
                                }}
                            </td>
                            <td class="text-center">
                                @if($transaksi->status == 1)
                                    <span class="badge bg-success">Selesai</span>
                                @else
                                    <span class="badge bg-warning">Antrian</span>
                                @endif
                            </td>
                            <td class="text-center">
                                Rp{{ number_format($transaksi->total_pembayaran) }}
                            </td>
                            <td class="text-center">
                                <a class="text-black btn btn-info" href="{{ route('transaksi.show', ['transaksi' => $transaksi->id]) }}"><i class="fa-solid fa-info"></i> Info</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <div class="mt-3">
            {{ $transaksis->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
        var msg = '{{ Session::get('alert') }}';

        var exist = '{{ Session::has('alert') }}';

        if (exist) {
            alert(msg);
        }
    </script>
@endsection
