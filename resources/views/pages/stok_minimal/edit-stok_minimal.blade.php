@extends('layouts.master')

@php($title = $title ?? 'Ubah Satuan Minimal')

@section('content')
<div class="card p-4">
    <h3 class="text-black fw-bold">Ubah Satuan Minimal</h3>
    <form method="POST" action="{{ route('stok-minimal.update', $stokMinimal) }}" class="mt-2">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Grup Bahan Baku</label>
            <select name="grup_bahan_baku_id" class="form-select" required>
                @foreach ($grupBahanBakus as $grup)
                    <option value="{{ $grup->id }}" {{ $stokMinimal->grup_bahan_baku_id==$grup->id?'selected':'' }}>{{ $grup->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Lokasi</label>
            <select name="lokasi" class="form-select" required>
                <option value="dapur" {{ $stokMinimal->lokasi==='dapur'?'selected':'' }}>Dapur</option>
                <option value="gudang" {{ $stokMinimal->lokasi==='gudang'?'selected':'' }}>Gudang</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Satuan</label>
            <select name="satuan_id" class="form-select" required>
                @foreach ($satuans as $s)
                    <option value="{{ $s->id }}" {{ $stokMinimal->satuan_id==$s->id?'selected':'' }}>{{ $s->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah Minimal</label>
            <input type="number" min="0" class="form-control" name="jumlah_minimal" value="{{ $stokMinimal->jumlah_minimal }}" required />
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stok-minimal.index') }}" class="btn btn-light">Batal</a>
            <button class="btn btn-dark">Simpan</button>
        </div>
    </form>
@endsection