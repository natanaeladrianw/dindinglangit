@extends('layouts.master')

@section('content')
    <style>
        /* Basic styling for readability, adjust as needed for your theme */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top; /* Align content to top for rowspan cells */
        }
        th {
            background-color: #f2f2f2;
        }
        .date-header {
            background-color: #e0f7fa; /* Light cyan for date header */
            font-weight: bold;
            text-align: center;
            padding: 10px;
        }
        .summary-row {
            background-color: #f0f4c3; /* Light yellow for daily summary */
            font-weight: bold;
        }
        .shift-detail-row {
            background-color: #ffffff; /* White for shift details */
        }
        .bahan-baku-detail {
            font-size: 0.9em;
            color: #555;
            margin-top: 5px;
            padding-left: 10px; /* Indent for clarity */
        }
        .bahan-baku-detail div {
            padding: 2px 0;
            border-bottom: 1px dashed #eee;
        }
        .bahan-baku-detail div:last-child {
            border-bottom: none;
        }
    </style>

    <div class="card p-4">
        <h3 class="text-black fw-bold">{{ $title }}</h3>

        {{-- Form Filter Tanggal --}}
        <form action="/laporan-penggunaan-bahan-baku" method="GET" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $startDate) }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Akhir:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $endDate) }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-dark w-100">Filter</button>
                </div>
            </div>
        </form>

        @if (empty($finalReport))
            <div class="alert alert-info" role="alert">
                Tidak ada data laporan untuk rentang tanggal ini.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th class="text-white text-center">Tanggal</th>
                            <th class="text-white">Kasir</th>
                            <th class="text-white text-end">Total Pembayaran</th>
                            <th class="text-white text-center">Jumlah Transaksi</th>
                            <th class="text-white text-center">Saldo Awal</th>
                            <th class="text-white text-center">Saldo Akhir</th>
                            <th class="text-white">Ringkasan Penggunaan Bahan Baku Harian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($finalReport as $date => $dateData)
                            @php
                                $numRowsForDate = max(count($dateData['shifts']), count($dateData['bahan_baku_daily_summary']));
                                if ($numRowsForDate == 0) $numRowsForDate = 1; // At least one row if no shifts/bahan baku
                            @endphp

                            <tr>
                                <td rowspan="{{ $numRowsForDate }}" class="date-header">
                                    {{ \Carbon\Carbon::parse($dateData['date'])->translatedFormat('d F Y') }}
                                    <br><br>
                                    <div class="p-1 bg-dark rounded text-light">
                                        <small class="fw-normal">Total Pembayaran:</small> <br>
                                        <span class="fw-normal">Rp{{ number_format($dateData['daily_total_pembayaran'], 0, ',', '.') }}</span>
                                    </div>
                                    <br>
                                    <div class="p-1 bg-dark rounded text-light">
                                        <small>Total Transaksi:</small> <br>
                                        <span class="fw-bold">{{ $dateData['daily_total_transaksi'] }}</span>
                                    </div>
                                </td>

                                {{-- Display first shift details --}}
                                @if (isset($dateData['shifts'][0]))
                                    <td class="shift-detail-row">{{ $dateData['shifts'][0]['kasir_name'] }}</td>
                                    <td class="shift-detail-row text-end">Rp{{ number_format($dateData['shifts'][0]['total_pembayaran_shift'], 0, ',', '.') }}</td>
                                    <td class="shift-detail-row text-center">{{ $dateData['shifts'][0]['total_transaksi_shift'] }}</td>
                                    <td class="shift-detail-row text-center">{{ number_format($dateData['shifts'][0]['saldo_awal'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="shift-detail-row text-center">{{ number_format($dateData['shifts'][0]['saldo_akhir'] ?? 0, 0, ',', '.') }}</td>
                                @else
                                    {{-- If no shifts for this date, provide empty cells for shift-related columns.
                                         There are 5 shift-related columns: Kasir, Total Pembayaran, Jumlah Transaksi, Saldo Awal, Saldo Akhir. --}}
                                    <td class="shift-detail-row" colspan="5"></td>
                                @endif

                                {{-- Display bahan baku summary for the whole day (rowspan) --}}
                                @if (isset($dateData['bahan_baku_daily_summary'][0]))
                                    <td rowspan="{{ $numRowsForDate }}" class="bahan-baku-summary-row">
                                        <div class="bahan-baku-detail">
                                            @foreach ($dateData['bahan_baku_daily_summary'] as $bahanBaku)
                                                <div>
                                                    {{ $bahanBaku['bahan_baku_name'] }}: <span class="fw-bold">{{ $bahanBaku['total_jumlah_pakai'] }} {{ $bahanBaku['satuan_name'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                @else
                                    <td rowspan="{{ $numRowsForDate }}" class="bahan-baku-summary-row text-muted fst-italic text-center">
                                        Tidak ada penggunaan bahan baku.
                                    </td>
                                @endif
                            </tr>

                            {{-- Loop for remaining shifts and align with the rowspan columns.
                                 Crucially, these rows do NOT repeat the 'Tanggal' or 'Ringkasan Bahan Baku Harian' cells. --}}
                            @for ($i = 1; $i < $numRowsForDate; $i++)
                                <tr>
                                    @if (isset($dateData['shifts'][$i]))
                                        {{-- Only display the shift-related cells here, WITHOUT rowspan --}}
                                        <td class="shift-detail-row">{{ $dateData['shifts'][$i]['kasir_name'] }}</td>
                                        <td class="shift-detail-row text-end">Rp{{ number_format($dateData['shifts'][$i]['total_pembayaran_shift'], 0, ',', '.') }}</td>
                                        <td class="shift-detail-row text-center">{{ $dateData['shifts'][$i]['total_transaksi_shift'] }}</td>
                                        <td class="shift-detail-row text-center">{{ number_format($dateData['shifts'][$i]['saldo_awal'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="shift-detail-row text-center">{{ number_format($dateData['shifts'][$i]['saldo_akhir'] ?? 0, 0, ',', '.') }}</td>
                                    @else
                                        {{-- Empty cells for remaining rows if no more shifts, to fill the space for rowspan columns.
                                             Again, colspan of 5 for the 5 shift-related columns. --}}
                                        <td class="shift-detail-row" colspan="5"></td>
                                    @endif
                                    {{-- The 'Ringkasan Penggunaan Bahan Baku Harian' column is NOT included here,
                                         as it's handled by rowspan from the first row of this date group. --}}
                                </tr>
                            @endfor
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
