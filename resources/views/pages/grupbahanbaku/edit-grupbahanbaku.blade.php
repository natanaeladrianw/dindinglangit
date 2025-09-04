@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Edit Grup Bahan Baku</h3>
        <form action="{{ route('grupbahanbaku.update', $grupbahanbaku->id) }}" method="post">
            @csrf
            @method('put')
            <label for="">Nama</label>
            <input type="text" name="nama" class="form-control mb-3" value="{{ $grupbahanbaku->nama }}">
            <label for="">Keterangan</label>
            <input type="text" name="keterangan" class="form-control mb-3" value="{{ $grupbahanbaku->keterangan }}">

            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>
@endsection
