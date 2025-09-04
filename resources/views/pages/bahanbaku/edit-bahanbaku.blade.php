@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Edit Bahan Baku</h3>
        <form action="{{ route('bahanbaku.update', $bahanbaku->id) }}" method="post">
            @csrf
            @method('put')
            <label for="">Grup Bahan Baku</label>
            <select class="form-select mb-3" name="grup_bahan_baku_id">
                @foreach ($grupBahanBakus as $grupBahanBaku)
                    <option value="{{ $grupBahanBaku->id }}"
                        {{ $grupBahanBaku->id == ($bahanbaku->grup_bahan_baku_id ?? '') ? 'selected' : '' }}>
                        {{ $grupBahanBaku->nama }}
                    </option>
                @endforeach
            </select>
            <label for="">Kategori</label>
            <select class="form-select mb-3" name="kategori_bahan_baku_id">
                @foreach ($kategori_bahan_baku as $kategori)
                    @if ($kategori->id == $bahanbaku->kategori_bahan_baku_id)
                        <option selected value="{{ $kategori->id }}">{{ $kategori->jenis_bahan_baku }}</option>
                    @else
                        <option value="{{ $kategori->id }}">{{ $kategori->jenis_bahan_baku }}</option>
                    @endif
                @endforeach
            </select>
            <label for="">Kode</label>
            <input type="text" name="kode" class="form-control mb-3" value="{{ $bahanbaku->kode }}">
            <label for="">Nama</label>
            <input type="text" name="nama" class="form-control mb-3" value="{{ $bahanbaku->nama }}">

            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>
@endsection
