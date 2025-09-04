@extends('layouts.master')

@section('content')
{{-- @dd($pembelianBahanBakus) --}}
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="m-0 me-2 text-black">Laporan Pembelian Bahan Baku</h5>
                    {{-- Form Filter Tanggal --}}
                    <form action="/laporan-pembelian-bahan-baku" method="GET" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Tanggal Mulai:</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $startDate) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Tanggal Akhir:</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $endDate) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nama_bahan_baku" class="form-label">Nama Bahan Baku:</label>
                                <input type="text" name="nama_bahan_baku" id="nama_bahan_baku" class="form-control" placeholder="Contoh: Gula" value="{{ request('nama_bahan_baku') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="kode_bahan_baku" class="form-label">Kode Bahan Baku:</label>
                                <input type="text" name="kode_bahan_baku" id="kode_bahan_baku" class="form-control" placeholder="Contoh: 001PWTR" value="{{ request('kode_bahan_baku') }}">
                            </div>
                        </div>
                        <div class="row g-3 align-items-end mt-2">
                            <div class="col-md-3">
                                <label for="supplier" class="form-label">Supplier:</label>
                                <input type="text" name="supplier" id="supplier" class="form-control" placeholder="Nama supplier" value="{{ request('supplier') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-center" style="padding-top: 2rem;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="show_all" name="show_all" {{ request('show_all') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label" for="show_all">
                                        Tampilkan semua bahan baku yang pernah dibeli
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 ms-auto">
                                <button type="submit" class="btn btn-dark w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="d-flex flex-column gap-1 mt-4 mb-3">
                        @php
                            $spending = 0;
                            foreach ($pembelianBahanBakus as $pembelian) {
                                $spending = $spending + $pembelian->harga;
                            }
                        @endphp
                        <h2 class="mb-2 text-muted">Rp{{ number_format($spending) }}</h2>
                        <span>Total Pembelian</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            {{ $pembelianBahanBakus->links('pagination::bootstrap-5') }}
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="per_page" class="form-label mb-0">Baris per halaman:</label>
                            <select id="per_page" name="per_page" class="form-select" style="width: auto;" form="perPageForm" onchange="document.getElementById('perPageForm').submit()">
                                @php($currentPerPage = request('per_page', 10))
                                @foreach ([5,10,25,50,100] as $size)
                                    <option value="{{ $size }}" {{ (int)$currentPerPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-white" style="white-space: nowrap;">Kode Bahan Baku</th>
                                    <th class="text-white">Nama Bahan Baku</th>
                                    <th class="text-white">Supplier</th>
                                    <th class="text-white text-end" style="white-space: nowrap;">Jumlah</th>
                                    <th class="text-white text-end">Harga</th>
                                    <th class="text-white" style="white-space: nowrap;">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pembelianBahanBakus as $pembelian)
                                    <tr>
                                        <td>{{ $pembelian->bahanBakus->kode }}</td>
                                        <td>{{ $pembelian->bahanBakus->nama }}</td>
                                        <td>{{ optional($pembelian->notaBelis->suppliers)->nama ?? '-' }}</td>
                                        <td class="text-end">
                                            {{ number_format($pembelian->jumlah) }}
                                            {{ $pembelian->bahanBakus->stokGudangs->first()?->satuans->nama }}
                                        </td>
                                        <td class="text-end">Rp{{ number_format($pembelian->harga) }}</td>
                                        <td>{{ optional($pembelian->notaBelis)->tanggal_transaksi ? Carbon\Carbon::parse($pembelian->notaBelis->tanggal_transaksi)->translatedFormat('l, d F Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Data tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            {{ $pembelianBahanBakus->links('pagination::bootstrap-5') }}
                        </div>
                        <form id="perPageForm" action="/laporan-pembelian-bahan-baku" method="GET">
                            <input type="hidden" name="start_date" value="{{ request('start_date', $startDate) }}">
                            <input type="hidden" name="end_date" value="{{ request('end_date', $endDate) }}">
                            <input type="hidden" name="nama_bahan_baku" value="{{ request('nama_bahan_baku') }}">
                            <input type="hidden" name="kode_bahan_baku" value="{{ request('kode_bahan_baku') }}">
                            <input type="hidden" name="supplier" value="{{ request('supplier') }}">
                            <input type="hidden" name="show_all" value="{{ request('show_all') ? 1 : 0 }}">
                            <input type="hidden" name="per_page" id="hidden_per_page" value="{{ request('per_page', 10) }}">
                        </form>
                    </div>
                    <script>
                        (function(){
                            const sel = document.getElementById('per_page');
                            const hidden = document.getElementById('hidden_per_page');
                            if (sel && hidden) {
                                sel.addEventListener('change', function(){
                                    hidden.value = this.value;
                                });
                            }
                        })();
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection
