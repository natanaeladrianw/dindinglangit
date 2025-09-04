@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Edit Transaksi</h3>
        <form action="{{ route('transaksi.update', $transaksi->id) }}" method="post">
            @csrf
            @method('put')
            <label for="">Status</label>
            <select name="status" class="form-select mb-3">
                @for ($i = 0; $i <= 1; $i++)
                    <option value="{{ $i }}">{{ $i == 0 ? 'Antrian' : 'Selesai' }}</option>
                @endfor
            </select>
            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>
@endsection
