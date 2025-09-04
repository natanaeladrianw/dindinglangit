@extends('layouts.master')

@section('content')
<div class="card p-4">
    <h3 class="text-black fw-bold">Stok Opname Dapur</h3>
    <div class="d-flex justify-content-between align-items-center w-100">
        <form method="GET" action="/stokopname" class="w-50">
            {{-- @csrf --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Pilih Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                    value="{{ request('tanggal') }}">
                </div>
                <div class="col-md-4 align-self-end">
                    <button type="submit" class="btn btn-dark">Filter</button>
                    <a href="/stokopname" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
        <a href="{{ route('stok_opname.export', ['tanggal' => request('tanggal')]) }}" class="btn btn-success w-30">
            <i class="fas fa-file-excel"></i>
            Export ke Excel
        </a>
    </div>
    <table class="table table-hover" id="stokOpname">
        <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Kode</th>
                <th class="text-white" scope="col">Bahan Baku</th>
                <th class="text-white text-center" scope="col">Satuan Kecil</th>
                <th class="text-white text-center" scope="col">Stok Awal</th>
                <th class="text-white text-center" scope="col">Stok Masuk</th>
                <th class="text-white text-center" scope="col">Stok Pakai</th>
                <th class="text-white text-center" scope="col">Sisa Fisik</th>
                <th class="text-white text-center" scope="col">Sisa Seharusnya</th>
                <th class="text-white text-center" scope="col">Selisih</th>
                <th class="text-white text-center" scope="col">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if($data->isEmpty())
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data stok opname.</td>
                </tr>
            @else
                @foreach($data as $item)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $item['kode'] }}</td>
                        <td>{{ $item['nama'] }}</td>
                        <td class="text-center">{{ $item['satuan'] ?? '-' }}</td>
                        <td class="text-center">{{ number_format($item['stok_awal']) }}</td>
                        <td class="text-center">{{ number_format($item['stok_masuk']) }}</td>
                        <td class="text-center">{{ number_format($item['stok_pakai']) }}</td>
                        <td class="text-center">{{ number_format($item['sisa_fisik']) }}</td>
                        <td class="text-center">{{ number_format($item['sisa_seharusnya']) }}</td>
                        @if ($item['selisih'] < 0)
                            <td class="text-center text-danger">
                                {{ number_format($item['selisih']) }}
                            </td>
                        @else
                            <td class="text-center">
                                {{ number_format($item['selisih']) }}
                            </td>
                        @endif
                        <td class="text-center">{{ $item['keterangan'] ?? '-' }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

<script>
    var msg = '{{ Session::get('alert') }}';
    if (msg) {
        alert(msg);
    }
</script>
@endsection
