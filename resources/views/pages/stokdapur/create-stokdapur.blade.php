@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Tambah Stok Dapur</h3>
        <form action="{{ route('stokdapur.store') }}" method="post" id="stokDapurForm">
            @csrf
            <label for="">Bahan Baku</label>
            <select class="form-select mb-3" name="bahan_baku_id" id="bahanBakuSelect">
                <option value="" selected>Pilih</option>
                @foreach ($bahanBakus as $bahanBaku)
                    @php
                        $stokGudang = $bahanBaku->stokGudangs->first();
                        $isExpired = $stokGudang && $stokGudang->tanggal_exp && \Carbon\Carbon::parse($stokGudang->tanggal_exp)->isPast();
                    @endphp
                    <option value="{{ $bahanBaku->id }}" 
                            data-expired="{{ $isExpired ? '1' : '0' }}"
                            {{ $isExpired ? 'disabled' : '' }}>
                        {{ $bahanBaku->kode }} - {{ $bahanBaku->nama }} 
                        (Stok Gudang: {{ $stokGudang?->jumlah == floor($stokGudang?->jumlah) ? number_format($stokGudang?->jumlah, 0, '.', ',') : number_format($stokGudang?->jumlah, 1, '.', ',') }} {{ $stokGudang?->satuans->nama ?? '' }})
                        @if($isExpired)
                            - SUDAH KADALUWARSA
                        @endif
                    </option>
                @endforeach
            </select>
            <label for="">Satuan</label>
            <select class="form-select mb-3" name="satuan_id">
                <option value="" selected>Pilih</option>
                @foreach ($satuans as $satuan)
                    <option value="{{ $satuan->id }}">{{ $satuan->nama }}</option>
                @endforeach
            </select>
            <label for="">Stok Awal Dapur</label>
            <input type="hidden" name="jumlah" id="stok_awal_hidden" min="0">
            <input type="text" inputMode="numeric" class="form-control mb-3 format-stok-awal" min="0">
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
            $('.format-stok-awal').each(function() {
                let initialValue = $(this).val();
                if (initialValue) {
                    $(this).val(formatNilai(initialValue));
                    $('#stok_awal_hidden').val(unformatNilai(initialValue));
                }
            });

            // Handle input baru
            $('.format-stok-awal').on('input', function() {
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
                $('#stok_awal_hidden').val(unformatNilai(value));
            });

            // Form validation untuk expired items
            $('#stokDapurForm').on('submit', function(e) {
                const selectedOption = $('#bahanBakuSelect option:selected');
                const isExpired = selectedOption.data('expired') === '1';
                
                if (isExpired) {
                    e.preventDefault();
                    alert('Bahan baku ini sudah kadaluwarsa dan tidak dapat dikirim ke dapur!');
                    return false;
                }
            });
        });
    </script>
@endsection
