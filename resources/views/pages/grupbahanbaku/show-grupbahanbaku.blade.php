@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ route('grupbahanbaku.index') }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Grup: {{ $grup->nama }}</h3>
        <p class="mb-3">Keterangan: {{ $grup->keterangan ?? '-' }}</p>

        <table class="table table-hover">
            <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Kode</th>
                <th class="text-white" scope="col">Nama Bahan Baku</th>
                <th class="text-white" scope="col">Kategori</th>
            </tr>
            </thead>
            <tbody>
                @forelse ($bahanBakus as $index => $bahan)
                    <tr>
                        <th scope="row">{{ $index + 1 }}</th>
                        <td>{{ $bahan->kode }}</td>
                        <td>{{ $bahan->nama }}</td>
                        <td>{{ $bahan->kategoriBahanBakus->jenis_bahan_baku ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Tidak ada bahan baku di grup ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
