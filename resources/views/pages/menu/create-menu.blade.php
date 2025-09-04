@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Add Menu</h3>
        <form action="{{ route('menu.store') }}" method="post" id="createMenuForm">
            @csrf
            <label for="">Kategori</label>
            <select class="form-select mb-3" name="kategori_menu_id">
                <option value="" selected>Pilih</option>
                @foreach ($kategori_menu as $kategori)
                    <option value="{{ $kategori->id }}">{{ $kategori->jenis_kategori_menu }}</option>
                @endforeach
            </select>
            <label for="">Nama</label>
            <input type="text" name="nama_item" class="form-control mb-3">
            <label for="">Harga</label>
            <input type="text" inputmode="numeric" name="harga" id="harga" class="form-control mb-3" min="0" placeholder="0">
            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hargaInput = document.getElementById('harga');
            const form = document.getElementById('createMenuForm');

            // Format input with thousand separators
            hargaInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value !== '') {
                    value = parseInt(value).toLocaleString('id-ID');
                    e.target.value = value;
                }
            });

            // Convert formatted value back to number before form submission
            form.addEventListener('submit', function(e) {
                const hargaValue = hargaInput.value.replace(/\D/g, '');
                if (hargaValue !== '') {
                    hargaInput.value = hargaValue;
                }
            });
        });
    </script>
@endsection
