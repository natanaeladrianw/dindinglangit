@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Add Grup Bahan Baku</h3>
        <form action="{{ route('grupbahanbaku.store') }}" method="post">
            @csrf
            <label for="">Nama</label>
            <input type="text" name="nama" class="form-control mb-3">
            <label for="">Keterangan</label>
            <input type="text" name="keterangan" class="form-control mb-3">

            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>
@endsection
