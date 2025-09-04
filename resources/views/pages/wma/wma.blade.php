@extends('layouts.master')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Prediksi Penggunaan Bahan Baku (WMA)</h2>

    {{-- Alert --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Form Filter --}}
    <form id="wmaForm" method="GET" action="{{ route('wma.prediksi') }}" class="row g-3 mb-2">
        <div class="col-md-3">
            <label for="start_date_historis" class="form-label">Tanggal Mulai Data Historis</label>
            <input type="date" name="start_date_historis" id="start_date_historis" class="form-control"
                value="{{ old('start_date_historis', request('start_date_historis')) }}" required>
        </div>

        <div class="col-md-3">
            <label for="end_date_historis" class="form-label">Tanggal Akhir Data Historis</label>
            <input type="date" name="end_date_historis" id="end_date_historis" class="form-control"
                value="{{ old('end_date_historis', request('end_date_historis')) }}" required>
        </div>

        <div class="col-md-3">
            <label for="tipe_hari" class="form-label">Tipe Hari Prediksi</label>
            <select name="tipe_hari" id="tipe_hari" class="form-select">
                <option value="weekday" {{ request('tipe_hari') == 'weekday' ? 'selected' : '' }}>
                    5 Hari Weekday (Senin–Jumat)
                </option>
                <option value="weekend" {{ request('tipe_hari') == 'weekend' ? 'selected' : '' }}>
                    2 Hari Weekend (Sabtu–Minggu)
                </option>
                <option value="libur_nasional" {{ request('tipe_hari') == 'libur_nasional' ? 'selected' : '' }}>
                    1 Hari Libur Nasional
                </option>
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-dark w-100">Hitung Prediksi</button>
        </div>
    </form>
    <div id="wmaAlert" class="alert alert-warning d-none mb-4"></div>

    <script>
        (function(){
            const form = document.getElementById('wmaForm');
            const startEl = document.getElementById('start_date_historis');
            const endEl = document.getElementById('end_date_historis');
            const tipeEl = document.getElementById('tipe_hari');
            const alertBox = document.getElementById('wmaAlert');

            function showAlert(message){
                if (!alertBox) return;
                alertBox.textContent = message;
                alertBox.classList.remove('d-none');
                alertBox.classList.remove('alert-success');
                alertBox.classList.add('alert-warning');
            }

            function hideAlert(){
                if (!alertBox) return;
                alertBox.classList.add('d-none');
                alertBox.textContent = '';
            }

            function validateDates(){
                const startVal = startEl.value;
                const endVal = endEl.value;
                const tipe = tipeEl.value;
                if (!startVal || !endVal || !tipe) {
                    return { valid: true };
                }

                const start = new Date(startVal + 'T00:00:00');
                const end = new Date(endVal + 'T00:00:00');
                const startDay = start.getDay(); // 0=Sun,1=Mon,...,6=Sat
                const endDay = end.getDay();

                // Ensure chronological order (optional guard)
                if (end < start) {
                    return { valid: false, message: 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.' };
                }

                if (tipe === 'weekday') {
                    if (startDay !== 1 || endDay !== 5) {
                        return { valid: false, message: 'Untuk prediksi weekday, pilih rentang dari hari Senin hingga hari Jumat.' };
                    }
                } else if (tipe === 'weekend') {
                    if (startDay !== 6 || endDay !== 0) {
                        return { valid: false, message: 'Untuk prediksi weekend, pilih rentang dari hari Sabtu hingga hari Minggu.' };
                    }
                } else if (tipe === 'libur_nasional') {
                    if (startVal !== endVal) {
                        return { valid: false, message: 'Untuk hari libur nasional, tanggal mulai dan tanggal akhir harus sama.' };
                    }
                }

                return { valid: true };
            }

            form.addEventListener('submit', function(e){
                hideAlert();
                const res = validateDates();
                if (!res.valid) {
                    e.preventDefault();
                    showAlert(res.message);
                }
            });

            // Live feedback when fields change
            [startEl, endEl, tipeEl].forEach(el => {
                el.addEventListener('change', () => {
                    const res = validateDates();
                    if (res.valid) hideAlert(); else showAlert(res.message);
                });
            });
        })();
    </script>

    {{-- Tabel Hasil --}}
    @if(isset($hasilPrediksi) && $hasilPrediksi->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    Hasil Prediksi dari {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }} ({{ ucfirst($tipeHari) }})
                    <a href="{{ route('wma_prediksi.export', [
                        'start_date_historis' => $startDate,
                        'end_date_historis' => $endDate,
                        'tipe_hari' => $tipeHari,
                    ]) }}" class="btn btn-success">
                        Export
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th class="text-light text-center">No</th>
                                <th class="text-light text-start">Nama Bahan Baku</th>
                                <th class="text-light text-center">Hasil Prediksi</th>
                                <th class="text-light text-center">Satuan</th>
                                <th class="text-light text-center">Konversi (Dibulatkan ke Atas)</th>
                                <th class="text-light text-center">Satuan Besar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hasilPrediksi as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item['nama'] }}</td>
                                    <td class="text-center">{{ $item['hasil'] }}</td>
                                    <td class="text-center">{{ $item['satuan'] }}</td>
                                    <td class="text-center">{{ isset($item['konversi']) ? ceil($item['konversi']) : 1 }}</td>
                                    <td class="text-center">{{ $item['satuan_besar'] ?? $item['satuan'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif(request()->has('start_date_historis'))
        <div class="alert alert-warning mt-4">
            Tidak ada data penggunaan bahan baku untuk rentang tanggal dan tipe hari yang dipilih.
        </div>
    @endif
</div>
@endsection
