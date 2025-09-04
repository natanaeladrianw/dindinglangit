@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <div class="d-flex justify-content-between">
            <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
                <i class="fa-solid fa-arrow-left me-2"></i> Back
            </a>
            <a href="{{ route('cetakinvoice', $transaksi->id) }}" class="btn btn-success" style="height: fit-content">
                <i class="fas fa-file-invoice"></i>
                Cetak Invoice
            </a>
        </div>

        <h3 class="text-black fw-bold mb-4">Detail Transaksi</h3>

        <div class="mb-4">
            <table class="table table-borderless">
                <tr>
                    <th>Kasir</th>
                    {{-- @if (Auth::user()->role == 'kasir')
                        <td>{{ $user->name ?? '-' }}</td>
                    @else
                    @endif --}}
                    <td>{{ $transaksi->users->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Total Pembayaran</th>
                    <td>Rp{{ number_format($transaksi->total_pembayaran, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Metode Pembayaran</th>
                    <td>{{ $transaksi->metode_pembayaran }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <th>Jam</th>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('H:i') }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <form action="{{ route('transaksi.update_status', $transaksi->id) }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select" style="max-width: 200px;">
                                <option value="0" {{ (int)$transaksi->status === 0 ? 'selected' : '' }}>Antrian</option>
                                <option value="1" {{ (int)$transaksi->status === 1 ? 'selected' : '' }}>Selesai</option>
                            </select>
                            <button type="submit" class="btn btn-dark">Ubah</button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Detail Item yang Dipesan --}}
        <div class="mt-5">
            <h5 class="fw-bold">Item Pesanan</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th class="text-light">No</th>
                            <th class="text-light">Nama Menu</th>
                            <th class="text-light">Jumlah</th>
                            <th class="text-light">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaksi->menus as $index => $menu)
                            <tr class="text-center">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $menu->nama_item }}</td>
                                <td>{{ $menu->pivot->jumlah_pesanan }}</td>
                                <td>{{ $menu->pivot->catatan_pesanan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada item dipesan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
