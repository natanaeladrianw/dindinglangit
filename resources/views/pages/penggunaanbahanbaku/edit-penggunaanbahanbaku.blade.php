@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Edit Penggunaan Bahan Baku</h3>
        <form action="{{ route('penggunaanbahanbaku.update', $penggunaanbahanbaku->id) }}" method="post" id="penggunaanForm">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Bahan Baku</label>
                <select class="form-select" name="bahan_baku_id" id="bahanBakuSelect" required>
                    <option value="" disabled>Pilih</option>
                    @foreach ($bahanBakus as $bahanBaku)
                        @php
                            // Hitung stok sementara untuk tampilan
                            $stokTampilan = $bahanBaku->stokDapurs->first()->jumlah ?? 0;
                        @endphp
                        <option value="{{ $bahanBaku->id }}"
                            data-stok="{{ $stokTampilan }}"
                            data-satuan="{{ $bahanBaku->stokDapurs->first()->satuan_id ?? '' }}"
                            {{ $bahanBaku->id == $penggunaanbahanbaku->bahan_baku_id ? 'selected' : '' }}>
                            {{ $bahanBaku->kode }} - {{ $bahanBaku->nama }} (Stok: {{ number_format($stokTampilan) }} {{ $bahanBaku->stokDapurs->first()->satuans->nama ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Jumlah Pakai</label>
                <input type="hidden" name="jumlah_pakai" id="jumlah_pakai_hidden" value="{{ $penggunaanbahanbaku->jumlah_pakai }}">
                <input type="text" inputmode="numeric" id="jumlahPakai" class="form-control jumlah-pakai"
                       value="{{ old('jumlah_pakai', $penggunaanbahanbaku->jumlah_pakai == floor($penggunaanbahanbaku->jumlah_pakai) ? number_format($penggunaanbahanbaku->jumlah_pakai, 0, '.', ',') : number_format($penggunaanbahanbaku->jumlah_pakai, 1, '.', ',')) }}">
            </div>

            <div class="mb-3">
                <label>Satuan Pakai</label>
                <select class="form-select" name="satuan_id" id="satuanSelect" required>
                    <option value="" disabled>Pilih</option>
                    @foreach ($satuans as $satuan)
                        <option value="{{ $satuan->id }}"
                            data-reference="{{ $satuan->reference_satuan_id }}"
                            data-nilai="{{ $satuan->nilai }}"
                            {{ $satuan->id == $penggunaanbahanbaku->satuan_id ? 'selected' : '' }}>
                            {{ $satuan->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Sisa Fisik</label>
                {{-- <small class="text-muted" id="sisaFisikDisplay"></small> --}}
                {{-- @dd(number_format($penggunaanbahanbaku->sisa_fisik, 1, '.', ',')) --}}
                <input type="hidden" name="sisa_fisik" id="sisa_fisik_hidden" value="{{ $penggunaanbahanbaku->sisa_fisik }}">
                <input type="text" inputmode="numeric" class="form-control sisa-fisik" value="{{ $penggunaanbahanbaku->sisa_fisik == floor($penggunaanbahanbaku->sisa_fisik) ? number_format($penggunaanbahanbaku->sisa_fisik, 0, '.', ',') : number_format($penggunaanbahanbaku->sisa_fisik, 1, '.', ',') }}">
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
            const satuanSelect = document.getElementById('satuanSelect');
            const sisaFisikDisplay = document.getElementById('sisaFisikDisplay');
            const sisaFisikHidden = document.getElementById('sisaFisik');

            // Konversi satuan
            function convertToBaseUnit(value, satuanId) {
                const option = satuanSelect.querySelector(`option[value="${satuanId}"]`);
                const referenceId = option?.dataset.reference;
                const nilai = parseFloat(option?.dataset.nilai) || 1;

                console.log("option", option);
                console.log("referenceId", referenceId);
                console.log("nilai", nilai);

                if (referenceId === "null") {
                    return value; // Satuan dasar (g/ml)
                } else {
                    return value * nilai; // Konversi ke satuan dasar
                }
            }

            // Hitung sisa fisik
            function calculateRemainingStock() {
                const selectedOption = bahanBakuSelect.options[bahanBakuSelect.selectedIndex];
                const stokSatuanId = bahanBakuSelect.selectedOptions[0]?.dataset.satuan;
                const stokTampilan = parseFloat(selectedOption.dataset.stok) || 0;
                const pakaiSatuanId = satuanSelect.value;
                const jumlahPakai = parseFloat(jumlahPakaiInput.value) || 0;

                // Konversi semua ke satuan dasar (g/ml)
                const stokInBase = convertToBaseUnit(stokTampilan, stokSatuanId);
                const pakaiInBase = convertToBaseUnit(jumlahPakai, pakaiSatuanId);

                console.log(selectedOption);
                console.log(stokSatuanId);
                console.log(stokTampilan);
                console.log(pakaiSatuanId);
                console.log(jumlahPakai);

                // Hitung sisa dalam satuan dasar
                const sisaInBase = stokInBase - pakaiInBase;

                // Konversi kembali ke satuan stok
                const stokSatuanOption = satuanSelect.querySelector(`option[value="${stokSatuanId}"]`);
                const stokSatuanNilai = parseFloat(stokSatuanOption?.dataset.nilai) || 1;
                const sisaInOriginalUnit = sisaInBase / stokSatuanNilai;
                const sisaSeharusnya = sisaInOriginalUnit + pakaiInBase;

                // Tampilkan hasil
                const satuanNama = selectedOption.text.match(/\(Stok:.*?(\w+)\)/)[1] || '';
                sisaFisikDisplay.innerHTML = `(Seharusnya: ${sisaSeharusnya.toFixed(2)} ${satuanNama})`;
                sisaFisikHidden.value = sisaInOriginalUnit;
            }

            // Event listeners
            [bahanBakuSelect, jumlahPakaiInput, satuanSelect].forEach(el => {
                el.addEventListener('change', calculateRemainingStock);
                el.addEventListener('input', calculateRemainingStock);
            });

            // Inisialisasi awal
            calculateRemainingStock();
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
            $('.sisa-fisik').each(function() {
                let initialValue = $(this).val();
                if (initialValue) {
                    $(this).val(formatNilai(initialValue));
                    $('#sisa_fisik_hidden').val(unformatNilai(initialValue));
                }
            });

            // Handle input baru
            $('.sisa-fisik').on('input', function() {
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
                $('#sisa_fisik_hidden').val(unformatNilai(value));
            });

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
