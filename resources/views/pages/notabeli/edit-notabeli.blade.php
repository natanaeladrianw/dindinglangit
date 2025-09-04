@extends('layouts.master')

@section('content')
<div class="card p-4">
    <a href="{{ route('notabeli.index') }}" class="btn btn-dark mb-4" style="width: fit-content">
        <i class="fa-solid fa-arrow-left me-2"></i> Back
    </a>

    <h3 class="text-black fw-bold">Edit Nota Beli</h3>
    {{-- @dd($notabeli->bahanBakus->latest()->first()->stokGudangs->first()); --}}
    <form action="{{ route('notabeli.update', $notabeli->id) }}" method="post">
        @csrf
        @method('put')

        <!-- Hidden fields for original values -->
        <input type="hidden" name="nota_beli_id" value="{{ $notabeli->nota_beli_id }}">
        <input type="hidden" name="original_bahan_baku_id" value="{{ $notabeli->bahan_baku_id }}">
        <input type="hidden" name="original_jumlah"
            value="{{ $notabeli->bahanBakus->first()->stokGudangs->first()->jumlah ?? 0 }}">
        <div class="mb-3">
            <label class="form-label">Bahan Baku</label>
            <select class="form-select" name="bahan_baku_id" required>
                @foreach ($bahanBakus as $bahanBaku)
                    <option value="{{ $bahanBaku->id }}"
                        {{ $bahanBaku->id == $notabeli->bahan_baku_id ? 'selected' : '' }}>
                        {{ $bahanBaku->kode }} - {{ $bahanBaku->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Jumlah</label>
            <input type="hidden" name="jumlah" inputmode="numeric" class="jumlah_hidden" min="0" value="{{ number_format($notabeli->jumlah) }}">
            <input type="text" inputmode="numeric" class="form-control jumlah"
                   value="{{ $notabeli->jumlah ?? 0 }}" required min="0">
        </div>
        {{-- @dd($notabeli->bahanBakus->stokGudangs->first()) --}}
        <div class="mb-3">
            <label class="form-label">Satuan</label>
            <select class="form-select" name="satuan_id" required>
                @foreach ($satuans as $satuan)
                    <option value="{{ $satuan->id }}"
                        {{ $satuan->id == ($notabeli->bahanBakus->stokGudangs->first()?->satuan_id ?? '') ? 'selected' : '' }}>
                        {{ $satuan->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Supplier</label>
            <select class="form-select" name="supplier_id" required>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}"
                        {{ $supplier->id == $notabeli->notaBelis->supplier_id ? 'selected' : '' }}>
                        {{ $supplier->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Harga</label>
            <input type="hidden" name="harga" inputmode="numeric" class="harga_hidden" min="0" value="{{ number_format($notabeli->harga) }}">
            <input type="text" inputmode="numeric" class="form-control mb-3 harga" min="0" value="{{ number_format($notabeli->harga) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Tanggal Transaksi</label>
            <input type="date" name="tanggal_transaksi" class="form-control mb-3" value="{{ substr($notabeli->notaBelis->tanggal_transaksi, 0, 10) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Tanggal Kadaluwarsa</label>
            <input type="date" name="tgl_exp" class="form-control mb-3" value="{{ substr($notabeli->tgl_exp, 0, 10) }}">
        </div>

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
