@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Add Satuan</h3>
        <form action="{{ route('satuan.store') }}" method="post">
            @csrf
            <label for="">Satuan</label>
            <input type="text" name="nama" class="form-control mb-3">
            <label for="">Satuan Kecil</label>
            <select class="form-select mb-3" name="reference_satuan_id">
                <option value="" selected>Pilih</option>
                @foreach ($satuans as $item)
                    <option value="{{ $item->id }}">
                        {{ $item->nama }}
                    </option>
                @endforeach
            </select>
            <label for="">Nilai</label>
            <input type="hidden" inputmode="numeric" name="nilai" id="nilai_hidden" class="form-control mb-3 nilai_hidden" min="0">
            <input type="text" inputmode="numeric" class="form-control mb-3 nilai" min="0">
            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>

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
            $('.nilai').each(function() {
                let initialValue = $(this).val();
                if (initialValue) {
                    $(this).val(formatNilai(initialValue));
                    $('#nilai_hidden').val(unformatNilai(initialValue));
                }
            });

            // Handle input baru
            $('.nilai').on('input', function() {
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
                $('#nilai_hidden').val(unformatNilai(value));
            });
        });
    </script>

    <script>
        var msg = '{{ Session::get('alert') }}';

        var exist = '{{ Session::has('alert') }}';

        if (exist) {
            alert(msg);
        }
    </script>
@endsection
