@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Edit Menu</h3>
        <form action="{{ route('menu.update', $menu->id) }}" method="post" id="editMenuForm">
            @csrf
            @method('put')
            <label for="">Kategori</label>
            <select class="form-select mb-3" name="kategori_menu_id">
                @foreach ($kategori_menu as $kategori)
                    @if ($kategori->id == $menu->kategori_menu_id)
                        <option selected value="{{ $kategori->id }}">{{ $kategori->jenis_kategori_menu }}</option>
                    @else
                        <option value="{{ $kategori->id }}">{{ $kategori->jenis_kategori_menu }}</option>
                    @endif
                @endforeach
            </select>
            <label for="">Nama</label>
            <input type="text" name="nama_item" class="form-control mb-3" value="{{ $menu->nama_item }}">
            <label for="">Harga</label>
            <input type="text" inputmode="numeric" name="harga" id="harga" class="form-control mb-3" value="{{ $menu->harga }}" min="0">
            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hargaInput = document.getElementById('harga');
            const form = document.getElementById('editMenuForm');

            // Format existing value on page load
            if (hargaInput.value) {
                const numericValue = hargaInput.value.replace(/\D/g, '');
                if (numericValue !== '') {
                    hargaInput.value = parseInt(numericValue).toLocaleString('id-ID');
                }
            }

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
