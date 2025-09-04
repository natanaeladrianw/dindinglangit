@extends('layouts.master')

@php($title = $title ?? 'Tambah Satuan Minimal')

@section('content')
<div class="card p-4">
    <h3 class="text-black fw-bold">Tambah Satuan Minimal</h3>
    <form method="POST" action="{{ route('stok-minimal.store') }}" class="mt-2">
        @csrf
        <div class="mb-3">
            <label class="form-label">Grup Bahan Baku</label>
            <select name="grup_bahan_baku_id" class="form-select" required>
                <option value="">-- Pilih --</option>
                @foreach ($grupBahanBakus as $grup)
                    <option value="{{ $grup->id }}">{{ $grup->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Lokasi</label>
            <select name="lokasi" class="form-select" required>
                <option value="dapur">Dapur</option>
                <option value="gudang">Gudang</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Satuan</label>
            <select name="satuan_id" class="form-select" required>
                <option value="">-- Pilih --</option>
                @foreach ($satuans as $s)
                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah Minimal</label>
            <input type="number" min="0" class="form-control" name="jumlah_minimal" required />
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stok-minimal.index') }}" class="btn btn-light">Batal</a>
            <button class="btn btn-dark">Simpan</button>
        </div>
    </form>
</div>
@endsection