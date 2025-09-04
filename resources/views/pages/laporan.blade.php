@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <form method="GET" class="p-3 row mb-3 g-3">
                    <div class="col-md-4">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="nota_start" class="form-control" value="{{ request('nota_start') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="nota_end" class="form-control" value="{{ request('nota_end') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark me-2">Filter</button>
                        <a href="/laporan" class="btn btn-secondary">Reset</a>
                    </div>
                </form>

                <div class="card-body">
                    <div class="card-title mb-0">
                        <h5 class="m-0 mb-4 me-2 text-black">Laporan Nota Beli</h5>
                    </div>
                    <table class="table table-hover mb-4">
                        <thead>
                            <th class="fw-semibold text-dark text-center">No.</th>
                            <th class="fw-semibold text-dark">Produk</th>
                            <th class="fw-semibold text-dark">Supplier</th>
                            <th class="fw-semibold text-dark text-center">Tanggal Transaksi</th>
                        </thead>
                        <tbody>
                            @foreach ($notaBelis as $notaBeli)
                                <tr>
                                    <td class="text-center">
                                        <span class="text-muted">
                                            {{ $loop->iteration }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="">
                                            <h6 class="mb-0">{{ $notaBeli->bahanBakus->nama }}</h6>
                                            <small class="text-muted">{{ $notaBeli->bahanBakus->kode }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="">
                                            <h6 class="mb-0">{{ $notaBeli->notaBelis->suppliers->nama }}</h6>
                                            <small class="text-muted">{{ $notaBeli->notaBelis->suppliers->kontak }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="user-progress">
                                            <small class="text-muted">{{ Carbon\Carbon::parse($notaBeli->notaBelis->tanggal_transaksi)->format('d-m-Y') }}</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $notaBelis->appends(['pembelian_page' => $pembelianBahanBakus->currentPage()] + request()->all())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <form method="GET" class="p-3 row mb-3 g-3">
                    <div class="col-md-4">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="beli_start" class="form-control" value="{{ request('beli_start') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="beli_end" class="form-control" value="{{ request('beli_end') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark me-2">Filter</button>
                        <a href="/laporan" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
                <div class="card-body">
                    <h5 class="m-0 me-2 text-black">Laporan Pembelian Bahan Baku</h5>
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
                    <ul class="p-0 m-0">
                        @foreach ($pembelianBahanBakus as $pembelian)
                            <li class="d-flex align-items-center mb-4">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-dark">
                                        <i class="fa-solid fa-box-open"></i>
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $pembelian->bahanBakus->nama }}</h6>
                                        <small class="text-muted">{{ $pembelian->bahanBakus->kode }}</small>
                                    </div>
                                    <div class="user-progress">
                                        <small class="text-muted">{{ $pembelian->bahanBakus->harga }}</small>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    {{ $pembelianBahanBakus->appends(['nota_beli_page' => $notaBelis->currentPage()] + request()->all())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
