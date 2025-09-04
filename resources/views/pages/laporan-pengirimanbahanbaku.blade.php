@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="m-0 me-2 text-black">Laporan Pengiriman Bahan Baku (Gudang → Dapur)</h5>
                    <form action="/laporan-pengiriman-bahan-baku" method="GET" class="mb-4">
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
                                <input type="text" name="kode_bahan_baku" id="kode_bahan_baku" class="form-control" placeholder="Contoh: BB-001" value="{{ request('kode_bahan_baku') }}">
                            </div>
                        </div>
                        <div class="row g-3 align-items-end mt-2">
                            <div class="col-md-3">
                                <label for="user" class="form-label">Petugas:</label>
                                <input type="text" name="user" id="user" class="form-control" placeholder="Nama petugas" value="{{ request('user') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-center" style="padding-top: 2rem;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="show_all" name="show_all" {{ request('show_all') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label" for="show_all">
                                        Tampilkan semua pengiriman
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 ms-auto">
                                <button type="submit" class="btn btn-dark w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            {{ $notaKirims->links('pagination::bootstrap-5') }}
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
                                    <th class="text-white" style="white-space: nowrap;">Tanggal</th>
                                    <th class="text-white" style="white-space: nowrap;">Kode</th>
                                    <th class="text-white">Nama Bahan Baku</th>
                                    <th class="text-white text-end" style="white-space: nowrap;">Stok Awal</th>
                                    <th class="text-white text-end">Jumlah</th>
                                    <th class="text-white" style="white-space: nowrap;">Satuan</th>
                                    <th class="text-white text-end" style="white-space: nowrap;">Sisa Stok</th>
                                    <th class="text-white">Petugas</th>
                                    <th class="text-white">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notaKirims as $nk)
                                    <tr>
                                        <td>{{ Carbon\Carbon::parse($nk->created_at)->translatedFormat('l, d F Y H:i') }}</td>
                                        <td>{{ $nk->bahanBakus->kode ?? '-' }}</td>
                                        <td>{{ $nk->bahanBakus->nama ?? '-' }}</td>
                                        <td class="text-end">
                                            @php($gudang = optional(optional($nk->bahanBakus)->stokGudangs)->first())
                                            @php($gudangSatuan = optional($gudang)->satuans)
                                            @php($stokSisaGudang = optional($gudang)->jumlah)
                                            @php($jumlahKirimDalamSatuanGudang = ($gudangSatuan && $nk->satuan_id) ? \App\Models\Satuan::convertAmount($nk->jumlah ?? 0, $nk->satuan_id, $gudangSatuan->id) : null)
                                            @php($stokAwalGudang = is_null($stokSisaGudang) || is_null($jumlahKirimDalamSatuanGudang) ? null : ($stokSisaGudang + $jumlahKirimDalamSatuanGudang))
                                            {{ is_null($stokAwalGudang) ? '-' : number_format($stokAwalGudang) }}
                                        </td>
                                        <td class="text-end">
                                            @php($jumlahDalamSatuanGudang = ($gudangSatuan && $nk->satuan_id) ? \App\Models\Satuan::convertAmount($nk->jumlah ?? 0, $nk->satuan_id, $gudangSatuan->id) : null)
                                            {{ is_null($jumlahDalamSatuanGudang) ? '-' : number_format($jumlahDalamSatuanGudang) }}
                                        </td>
                                        <td>{{ $gudangSatuan->nama ?? '-' }}</td>
                                        <td class="text-end">{{ is_null($stokSisaGudang ?? null) ? '-' : number_format($stokSisaGudang) }}</td>
                                        <td>{{ $nk->users->name ?? '-' }}</td>
                                        <td>{{ $nk->keterangan ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">Data tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            {{ $notaKirims->links('pagination::bootstrap-5') }}
                        </div>
                        <form id="perPageForm" action="/laporan-pengiriman-bahan-baku" method="GET">
                            <input type="hidden" name="start_date" value="{{ request('start_date', $startDate) }}">
                            <input type="hidden" name="end_date" value="{{ request('end_date', $endDate) }}">
                            <input type="hidden" name="nama_bahan_baku" value="{{ request('nama_bahan_baku') }}">
                            <input type="hidden" name="kode_bahan_baku" value="{{ request('kode_bahan_baku') }}">
                            <input type="hidden" name="user" value="{{ request('user') }}">
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


