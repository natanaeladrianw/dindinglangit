@extends('layouts.master')

@php($title = $title ?? 'Satuan Minimal')

@section('content')
<div class="card p-4">
    <h3 class="text-black fw-bold">Satuan Minimal</h3>
    <a href="{{ route('stok-minimal.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
        <i class="fa-solid fa-plus me-2"></i>
        Tambah Satuan Minimal
    </a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover" id="stok-minimal">
            <thead>
                <tr class="table-dark">
                    <th class="text-white text-center" scope="col">No.</th>
                    <th class="text-white text-center" scope="col">Grup Bahan Baku</th>
                    <th class="text-white text-center" scope="col">Lokasi</th>
                    <th class="text-white text-center" scope="col">Satuan</th>
                    <th class="text-white text-center" scope="col">Jumlah Minimal</th>
                    <th class="text-white text-center" scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if ($items->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data.</td>
                    </tr>
                @else
                    @foreach ($items as $i => $row)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="text-center">{{ $row->grupBahanBaku->nama ?? '-' }}</td>
                            <td class="text-center text-capitalize">{{ $row->lokasi }}</td>
                            <td class="text-center">{{ $row->satuans->nama ?? '-' }}</td>
                            <td class="text-center">{{ $row->jumlah_minimal }}</td>
                            <td class="text-center">
                                <a href="{{ route('stok-minimal.edit', $row) }}" class="text-black btn btn-warning me-2"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <form method="POST" action="{{ route('stok-minimal.destroy', $row) }}" class="d-inline" onsubmit="return confirm('Hapus entri ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-black btn btn-danger"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection