@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Add Kategori Menu</h3>
        <form action="{{ route('kategorimenu.store') }}" method="post">
            @csrf
            <label for="">Jenis Kategori Menu</label>
            <input type="text" name="jenis_kategori_menu" class="form-control mb-3">
            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>
@endsection
