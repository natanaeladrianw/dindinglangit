@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Add Nota Beli</h3>
        <form action="{{ route('notabeli.store') }}" method="post">
            @csrf
            <label for="">Bahan Baku</label>
            <select class="form-select mb-3" name="bahan_baku_id">
                <option value="" selected>Pilih</option>
                @foreach ($bahanBakus as $bahanBaku)
                    <option value="{{ $bahanBaku->id }}">{{ $bahanBaku->kode }} - {{ $bahanBaku->nama }}</option>
                @endforeach
            </select>
            <label for="">Supplier</label>
            <select class="form-select mb-3" name="supplier_id">
                <option value="" selected>Pilih</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                @endforeach
            </select>
            <label for="">Jumlah</label>
            <input type="hidden" name="jumlah" class="jumlah_hidden">
            <input type="text" inputmode="numeric" class="form-control mb-3 jumlah" min="0">
            <label for="">Satuan</label>
            <select class="form-select mb-3" name="satuan_id">
                <option value="" selected>Pilih</option>
                @foreach ($satuans as $satuan)
                    <option value="{{ $satuan->id }}">{{ $satuan->nama }}</option>
                @endforeach
            </select>
            <label for="">Harga</label>
            <input type="hidden" name="harga" class="harga_hidden">
            <input type="text" inputmode="numeric" class="form-control mb-3 harga" min="0">
            <label for="">Tanggal Transaksi</label>
            <input type="date" name="tanggal_transaksi" class="form-control mb-3">
            <label for="">Tanggal Kadaluwarsa</label>
            <input type="date" name="tgl_exp" class="form-control mb-3">
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
            // $('.jumlah').on('input', function () {
            //     let input = $(this);
            //     let value = input.val().replace(/\D/g, ''); // hanya angka

            //     // Format ribuan pakai titik
            //     let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

            //     input.val(formatted); // tampilkan ke input

            //     // Update hidden input (tanpa format)
            //     $('.jumlah_hidden').val(value);
            // });

            $('.harga').on('input', function () {
                let input = $(this);
                let value = input.val().replace(/\D/g, ''); // hanya angka

                // Format ribuan pakai titik
                let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                input.val(formatted); // tampilkan ke input

                // Update hidden input (tanpa format)
                $('.harga_hidden').val(value);
            });
        });
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
            $('.jumlah').each(function() {
                let initialValue = $(this).val();
                if (initialValue) {
                    $(this).val(formatNilai(initialValue));
                    $('.jumlah_hidden').val(unformatNilai(initialValue));
                }
            });

            // Handle input baru
            $('.jumlah').on('input', function() {
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
                $('.jumlah_hidden').val(unformatNilai(value));
            });
        });
    </script>
@endsection
