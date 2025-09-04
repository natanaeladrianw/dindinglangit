@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Tambah Penggunaan Bahan Baku</h3>
        <form action="{{ route('penggunaanbahanbaku.store') }}" method="post" id="penggunaanForm">
            @csrf
            <div class="mb-3">
                <label>Bahan Baku</label>
                <select class="form-select" name="bahan_baku_id" id="bahanBakuSelect" required>
                    <option value="" selected disabled>Pilih</option>
                    @foreach ($bahanBakus as $bahanBaku)
                        <option value="{{ $bahanBaku->id }}"
                            data-stok="{{ $bahanBaku->stokDapurs->first()->jumlah ?? 0 }}"
                            data-namasatuan="{{ $bahanBaku->stokDapurs->first()->satuans->nama ?? '' }}"
                            data-satuan="{{ $bahanBaku->stokDapurs->first()->satuan_id ?? '' }}">
                            {{ $bahanBaku->kode }} - {{ $bahanBaku->nama }} (Stok: {{ $bahanBaku->stokDapurs->first()->jumlah == floor($bahanBaku->stokDapurs->first()->jumlah) ? number_format($bahanBaku->stokDapurs->first()->jumlah, 0, '.', ',') : number_format($bahanBaku->stokDapurs->first()->jumlah, 1, '.', ',') }} {{ $bahanBaku->stokDapurs->first()->satuans->nama ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Jumlah Pakai</label>
                <input type="hidden" name="jumlah_pakai" id="jumlah_pakai_hidden">
                <input type="text" inputmode="numeric" id="jumlahPakai" class="form-control jumlah-pakai" min="0.01" step="0.01" required>
            </div>

            <div class="mb-3">
                <label>Satuan Pakai</label>
                <input type="hidden" name="satuan_id" id="satuanPakai" class="form-control" value="" readonly>
                <input type="text" id="satuanPakaiDisplay" class="form-control" value="" readonly>
                {{-- <select class="form-select" name="satuan_id" id="satuanSelect" required readonly>
                    <option value="" selected disabled>Pilih</option>
                    @foreach ($satuans as $satuan)
                        <option value="{{ $satuan->id }}" data-reference="{{ $satuan->reference_satuan_id }}" data-nilai="{{ $satuan->nilai }}">
                            {{ $satuan->nama }}
                        </option>
                    @endforeach
                </select> --}}
            </div>

            <div class="mb-3">
                <label>Sisa Fisik</label>
                <input type="hidden" id="sisaFisik" name="sisa_fisik" class="form-control" value="" readonly>
                <input type="text" id="sisaFisikText" class="form-control" value="" readonly>
            </div>

            <div class="mb-3">
                <label>Keterangan</label>
                <input type="text" name="keterangan" class="form-control" value="">
            </div>

            <button type="submit" class="btn btn-dark">Simpan</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            const bahanBakuSelect = document.getElementById('bahanBakuSelect');
            const jumlahPakaiInput = document.getElementById('jumlahPakai');
            const satuanPakai = document.getElementById('satuanPakai');
            const satuanPakaiDisplay = document.getElementById('satuanPakaiDisplay');
            // const satuanSelect = document.getElementById('satuanSelect');
            const sisaFisikDisplay = document.getElementById('sisaFisikDisplay');
            const sisaFisikHidden = document.getElementById('sisaFisik');
            const sisaFisikText = document.getElementById('sisaFisikText');

            function number_format(number, decimals, dec_point, thousands_sep) {
                // Strip all characters but numerical ones.
                number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                var n = !isFinite(+number) ? 0 : +number,
                    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                    s = '',
                    toFixedFix = function (n, prec) {
                        var k = Math.pow(10, prec);
                        return '' + Math.round(n * k) / k;
                    };
                // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                if (s[0].length > 3) {
                    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                }
                if ((s[1] || '').length < prec) {
                    s[1] = s[1] || '';
                    s[1] += new Array(prec - s[1].length + 1).join('0');
                }
                return s.join(dec);
            }

            // Hitung sisa fisik
            function calculateSisa() {
                const stok = parseFloat(bahanBakuSelect.selectedOptions[0]?.dataset.stok) || 0;
                const stokSatuanId = bahanBakuSelect.selectedOptions[0]?.dataset.satuan;
                const stokSatuanNama = bahanBakuSelect.selectedOptions[0]?.dataset.namasatuan || '';
                const jumlahPakai = parseFloat(jumlahPakaiInput.value.replace(/,/g, '')) || 0;
                satuanPakai.value = stokSatuanId
                satuanPakaiDisplay.value = stokSatuanNama

                console.log(stok);
                console.log(stokSatuanId);
                console.log(stokSatuanNama);
                console.log("jumlahPakai: ", jumlahPakai);

                const sisaInOriginalUnit = stok - jumlahPakai;

                // Tampilkan hasil
                sisaFisikHidden.value = sisaInOriginalUnit;
                sisaFisikText.value = `${number_format(sisaInOriginalUnit)}`;
            }

            bahanBakuSelect.addEventListener('change', calculateSisa);
            jumlahPakaiInput.addEventListener('input', calculateSisa);
            satuanSelect.addEventListener('change', calculateSisa);
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
            $('.jumlah-pakai').each(function() {
                let initialValue = $(this).val();
                if (initialValue) {
                    $(this).val(formatNilai(initialValue));
                    $('#jumlah_pakai_hidden').val(unformatNilai(initialValue));
                }
            });

            // Handle input baru
            $('.jumlah-pakai').on('input', function() {
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
                $('#jumlah_pakai_hidden').val(unformatNilai(value));
            });
        });
    </script>
@endsection
