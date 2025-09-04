@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Tambah Stok Dapur</h3>
        <form action="/tambah-stok-dapur/{{ $stokdapur->id }}" method="post">
            @csrf
            @php
                $bahanBaku = \App\Models\BahanBaku::find($stokdapur->bahanBakus->id);
            @endphp
            <label for="">Bahan Baku</label>
            <input type="hidden" class="form-control mb-3" name="bahan_baku_id" value="{{ $stokdapur->bahanBakus->id }}" readonly>
            <input type="text" class="form-control mb-3" value="{{ $stokdapur->bahanBakus->kode }} - {{ $stokdapur->bahanBakus->nama }} (Stok Gudang: {{ $bahanBaku->stokGudangs->first()?->jumlah == floor($bahanBaku->stokGudangs->first()?->jumlah) ? number_format($bahanBaku->stokGudangs->first()?->jumlah, 0, '.', ',') : number_format($bahanBaku->stokGudangs->first()?->jumlah, 1, '.', ',') }} {{ $bahanBaku->stokGudangs->first()?->satuans->nama ?? '' }})" readonly>
            {{-- <select class="form-select mb-3" name="bahan_baku_id">
                @foreach ($bahanBakus as $bahanBaku)
                    <option value="{{ $bahanBaku->id }}"
                        {{ $bahanBaku->id == ($stokdapur->bahan_baku_id ?? '') ? 'selected' : '' }}>
                        {{ $bahanBaku->nama }}
                    </option>
                @endforeach
            </select> --}}
            <label for="">Satuan</label>
            <input type="hidden" class="form-control mb-3" name="satuan_id" value="{{ $stokdapur->satuans->id }}" readonly>
            <input type="text" class="form-control mb-3" value="{{ $stokdapur->satuans->nama }}" readonly>
            {{-- <select class="form-select mb-3" name="satuan_id">
                @foreach ($satuans as $satuan)
                    <option value="{{ $satuan->id }}"
                        {{ $satuan->id == ($stokdapur->satuan_id ?? '') ? 'selected' : '' }}>
                        {{ $satuan->nama }}
                    </option>
                @endforeach
            </select> --}}
            <label for="">Tambah Stok Dapur</label>
            <small class="text-muted">(Stok dapur saat ini: {{ $stokdapur->jumlah == floor($stokdapur->jumlah) ? number_format($stokdapur->jumlah, 0, '.', ',') : number_format($stokdapur->jumlah, 1, '.', ',') }} {{ $stokdapur->satuans->nama }})</small>
            <input type="hidden" name="jumlah" id="stok_hidden" min="0">
            <input type="text" inputMode="numeric" class="form-control mb-3 format-stok" min="0">
            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>

    <script>
        var msg = '{{ Session::get('alert') }}';

        var exist = '{{ Session::has('alert') }}';

        if (exist) {
            alert(msg);
        }
    </script>

    <script>
        $(document).ready(function () {
            // Fungsi untuk memformat nilai
            function formatNilai(value) {
                // Hapus semua format sebelumnya (koma/titik kecuali desimal)
                let num = value.toString().replace(/,/g, '');

                // Pisahkan bagian desimal jika ada
                let parts = num.split('.');

                // Format bagian integer dengan separator ribuan
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                // Gabungkan kembali jika ada desimal
                return parts.length > 1 ? parts.join('.') : parts[0];
            }

            // Fungsi untuk unformat nilai (untuk disimpan)
            function unformatNilai(value) {
                return value.toString().replace(/,/g, '');
            }

            // Format nilai awal saat halaman dimuat
            $('.format-stok').each(function() {
                let initialValue = $(this).val();
                if (initialValue) {
                    $(this).val(formatNilai(initialValue));
                    $('#stok_hidden').val(unformatNilai(initialValue));
                }
            });

            // Handle input baru
            $('.format-stok').on('input', function() {
                let input = $(this);
                let value = input.val();

                // Biarkan angka, titik desimal, dan koma (tapi bersihkan dulu)
                value = value.replace(/[^\d.,]/g, '');

                // Pastikan hanya satu titik desimal
                value = value.replace(/,/g, ''); // Hapus semua koma dulu
                let decimalCount = (value.match(/\./g) || []).length;
                if (decimalCount > 1) {
                    value = value.substring(0, value.lastIndexOf('.'));
                }

                // Format nilai
                let formatted = formatNilai(value);
                input.val(formatted);

                // Update hidden input (tanpa format)
                $('#stok_hidden').val(unformatNilai(value));
            });
        });
    </script>
@endsection
