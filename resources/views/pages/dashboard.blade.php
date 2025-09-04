@extends('layouts.master')

@section('content')
    <div class="row">
        @if (Auth::user()->role == 'admin_gudang')
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title mb-0">
                                <h5 class="m-0 mb-4 me-2 text-black">Stok Dapur Menipis!</h5>
                            </div>
                            <table class="table table-hover mb-4">
                                <thead>
                                    <th class="fw-semibold text-dark text-center">No.</th>
                                    <th class="fw-semibold text-dark">Produk</th>
                                    <th class="fw-semibold text-dark text-center">Jumlah</th>
                                    <th class="fw-semibold text-dark text-center">Satuan</th>
                                    <th class="fw-semibold text-dark text-center">Status & Aksi</th>
                                </thead>
                                <tbody>
                                    @if ($stokDapurMenipis->isEmpty())
                                        <tr>
                                            <td colspan="5" class="text-center">Tidak ada stok yang menipis</td>
                                        </tr>
                                    @else
                                        @foreach ($stokDapurMenipis as $index => $dapur)
                                            <tr>
                                                <td class="text-center">
                                                    <span class="text-muted">
                                                        {{ $index + 1 }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        <h6 class="mb-0">{{ $dapur['bahanBakus']->nama }}</h6>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="">
                                                        <h6 class="mb-0">{{ number_format($dapur['jumlah'], 0, '.', ',') }}</h6>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="user-progress">
                                                        <small class="text-muted">{{ $dapur['satuans']->nama }}</small>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column align-items-center gap-2">
                                                        <div class="">
                                                            @if($dapur['pengajuan_restok'] == 1)
                                                                <span class="badge bg-success">Sudah Diajukan</span>
                                                            @else
                                                                <span class="badge bg-warning">Belum Diajukan</span>
                                                            @endif
                                                        </div>
                                                        <div class="">
                                                            <a href="{{ route('stokdapur.create') }}" class="btn btn-dark btn-sm" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: none; padding: 8px 16px; font-weight: 500;">
                                                                <i class="fa-solid fa-plus"></i> Tambah Stok
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="m-0 me-2 text-black">Stok Gudang Menipis!</h5>
                            <table class="table table-hover mb-4">
                                <thead>
                                    <th class="fw-semibold text-dark text-center">No.</th>
                                    <th class="fw-semibold text-dark">Produk</th>
                                    <th class="fw-semibold text-dark text-center">Jumlah</th>
                                    <th class="fw-semibold text-dark text-center">Satuan</th>
                                    <th class="fw-semibold text-dark text-center">Aksi</th>
                                </thead>
                                <tbody>
                                    @if ($stokGudangMenipis->isEmpty())
                                        <tr>
                                            <td colspan="5" class="text-center">Tidak ada stok yang menipis</td>
                                        </tr>
                                    @else
                                        @foreach ($stokGudangMenipis as $index => $gudang)
                                            <tr>
                                                <td class="text-center">
                                                    <span class="text-muted">
                                                        {{ $index + 1 }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        <h6 class="mb-0">{{ $gudang['bahanBakus']->nama }}</h6>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="">
                                                        <h6 class="mb-0">{{ number_format($gudang['jumlah'] / $gudang['satuans']->getKonversiKeTerkecil(), 0, '.', ',') }}</h6>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="user-progress">
                                                        <small class="text-muted">{{ $gudang['satuans']->nama }}</small>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="user-progress">
                                                        <a href="{{ route('bahanbaku.create') }}" class="btn btn-dark btn-sm" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: none; padding: 8px 16px; font-weight: 500;">
                                                            <i class="fa-solid fa-plus"></i> Tambah Stok
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @elseif (Auth::user()->role == 'admin_dapur')
            {{-- @dd($data) --}}
            <div class="row mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title mb-0">
                            <h5 class="m-0 mb-4 me-2 text-black">Selisih Stok!</h5>
                        </div>
                        <table class="table table-hover mb-4">
                            <thead>
                                <th class="fw-semibold text-dark text-center">No.</th>
                                <th class="fw-semibold text-dark text-center">Kode Barang</th>
                                <th class="fw-semibold text-dark">Bahan Baku</th>
                                <th class="fw-semibold text-dark text-center">Selisih</th>
                                <th class="fw-semibold text-dark text-center">Satuan</th>
                                <th class="fw-semibold text-dark text-center">Aksi</th>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <th scope="row" class="text-center">{{ $loop->iteration }}</th>
                                        <td class="text-center">P0001</td>
                                        <td>{{ $item['nama'] }}</td>
                                        <td class="text-center text-danger">
                                            {{ number_format($item['selisih']) }}
                                        </td>
                                        <td class="text-center">{{ $item['satuan'] ?? '-' }}</td>
                                        <td class="d-flex align-items-center justify-content-center">
                                            @php
                                                $today = Carbon\Carbon::now()->toDateString();
                                            @endphp

                                            <a href="{{ route('penggunaanbahanbaku.index', [
                                                    'start_date' => $today,
                                                    'end_date' => $today,
                                                    'sort' => 'ASC',
                                                    'search' => ''
                                                ]) }}"
                                                class="btn btn-dark me-2">
                                                Sesuaikan!
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title mb-0">
                            <h5 class="m-0 mb-4 me-2 text-black">Stok Dapur Menipis!</h5>
                        </div>
                        <table class="table table-hover mb-4">
                            <thead>
                                <th class="fw-semibold text-dark text-center">No.</th>
                                <th class="fw-semibold text-dark">Produk</th>
                                <th class="fw-semibold text-dark text-center">Jumlah</th>
                                <th class="fw-semibold text-dark text-center">Satuan</th>
                                <th class="fw-semibold text-dark text-center">Pengajuan Restok</th>
                                {{-- <th class="fw-semibold text-dark text-center">Aksi</th> --}}
                            </thead>
                            <tbody>
                                @if ($stokDapurMenipis->isEmpty())
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada stok yang menipis</td>
                                    </tr>
                                @else
                                    @foreach ($stokDapurMenipis as $index => $dapur)
                                        <tr>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    {{ $index + 1 }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="">
                                                    <h6 class="mb-0">{{ $dapur['bahanBakus']->nama }}</h6>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="">
                                                    <h6 class="mb-0">{{ number_format($dapur['jumlah'], 0, '.', ',') }}</h6>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="user-progress">
                                                    <small class="text-muted">{{ $dapur['satuans']->nama }}</small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="user-progress">
                                                    @if($dapur['pengajuan_restok'] == 1)
                                                        <button class="btn btn-secondary btn-sm" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: none; padding: 8px 16px; font-weight: 500;" disabled>
                                                            <i class="fa-solid fa-check"></i> Sudah Diajukan
                                                        </button>
                                                    @else
                                                        <a href="{{ route('ajukandapur', $dapur['stokDapur']->id) }}" class="btn btn-dark btn-sm" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: none; padding: 8px 16px; font-weight: 500;">
                                                            <i class="fa-solid fa-paper-plane"></i> Ajukan
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif (Auth::user()->role == 'kasir')
            {{-- Saldo Awal, dan Saldo Akhir --}}
            <div class="row mb-3">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title mb-0">
                                <h5 class="m-0 mb-4 me-2 text-black">Input Saldo Buka</h5>
                            </div>
                            <form action="/saldo-awal-kasir" method="post">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
                                <input type="hidden" name="saldo_awal" id="saldo_awal_hidden" value="{{ $lastShift->saldo_awal ?? 0 }}"> <!-- nilai bersih -->
                                <input type="text" inputmode="numeric" min="0" class="form-control format-number-awal" name="saldo_awal_formatted" value="{{ $lastShift->saldo_awal !== null ? number_format($lastShift->saldo_awal) : '' }}">
                                <button class="mt-3 btn btn-dark" type="submit">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title mb-0">
                                <h5 class="m-0 mb-4 me-2 text-black">Input Saldo Tutup</h5>
                            </div>
                            <form action="/saldo-akhir-kasir" method="post">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
                                <input type="hidden" name="saldo_akhir" id="saldo_akhir_hidden" value="{{ $lastShift->saldo_akhir ?? 0 }}">
                                <input type="text" inputmode="numeric" min="0" class="form-control format-number-akhir" name="saldo_akhir_formatted" value="{{ $lastShift->saldo_akhir !== null ? number_format($lastShift->saldo_akhir) : '' }}">
                                <button class="mt-3 btn btn-dark" type="submit">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Total Pemasukan TUNAI & QRIS --}}
            <div class="row mb-3">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title mb-0">
                                <h5 class="m-0 mb-4 me-2 text-black">Uang Masuk Hari Ini(Semua Shift)</h5>
                            </div>
                            <table class="table table-hover mb-4">
                                <thead>
                                    <th class="fw-semibold text-dark text-center">No.</th>
                                    <th class="fw-semibold text-dark">Metode Pembayaran</th>
                                    <th class="fw-semibold text-dark text-center">Total Pemasukan</th>
                                    <th class="fw-semibold text-dark text-center">Aksi</th>
                                </thead>
                                <tbody>
                                    {{-- @dd($qrisSetor) --}}
                                    @foreach ($transaksiMasuk as $transaksi)
                                        <tr>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    {{ $loop->iteration }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="">
                                                    <h6 class="mb-0">{{ $transaksi->metode_pembayaran ?? 'Belum ada transaksi' }}</h6>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="">
                                                    @if ($transaksi->metode_pembayaran == 'QRIS')
                                                        <h6 class="mb-0">{{ number_format($saldoQRIS) }}</h6>
                                                    @else
                                                        <h6 class="mb-0">{{ number_format($saldoCash) }}</h6>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="">
                                                    @if ($transaksi->metode_pembayaran == 'QRIS')
                                                        @if ((($transaksi->total ?? 0) - ($qrisSetor->total ?? 0)) == 0)
                                                            <button class="btn btn-dark" type="submit" disabled>Setor Uang</button>
                                                        @else
                                                            <form action="/setoruang" method="post">
                                                                @csrf
                                                                <input type="hidden" class="form-control" name="metode_pembayaran" value="{{ $transaksi->metode_pembayaran }}">
                                                                <input type="hidden" class="form-control" name="total_pembayaran" value="{{ $saldoQRIS }}">
                                                                <button class="btn btn-dark" type="submit">Setor Uang</button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        @if ((($transaksi->total ?? 0) - ($cashSetor->total ?? 0)) == 0)
                                                            <button class="btn btn-dark" type="submit" disabled>Setor Uang</button>
                                                        @else
                                                            <a data-bs-toggle="modal" data-bs-target="#setorUang" class="btn btn-dark text-light">
                                                                <div data-i18n="Basic">Setor Uang</div>
                                                            </a>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Riwayat Shift --}}
            <div class="row mb-3">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title mb-0">
                                <h5 class="m-0 mb-4 me-2 text-black">Riwayat Shift Hari Ini</h5>
                            </div>
                            <table class="table table-hover mb-4">
                                <thead>
                                    <th class="fw-semibold text-dark text-center">No.</th>
                                    <th class="fw-semibold text-dark">Nama</th>
                                    <th class="fw-semibold text-dark text-center">Jam Masuk</th>
                                    <th class="fw-semibold text-dark text-center">Jam keluar</th>
                                    <th class="fw-semibold text-dark text-center">Saldo Awal</th>
                                    <th class="fw-semibold text-dark text-center">Saldo Akhir</th>
                                </thead>
                                <tbody>
                                    @foreach ($shiftKasirs as $shiftKasir)
                                        <tr>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    {{ $loop->iteration }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="">
                                                    <h6 class="mb-0">{{ $shiftKasir->users->name }}</h6>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="">
                                                    <h6 class="mb-0">{{ \Carbon\Carbon::parse($shiftKasir->jam_masuk)->translatedFormat('H : i') }}</h6>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="">
                                                    <h6 class="mb-0">{{ $shiftKasir->jam_keluar ? \Carbon\Carbon::parse($shiftKasir->jam_keluar)->translatedFormat('H : i') : '-' }}</h6>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <h6 class="mb-0">{{ number_format($shiftKasir->saldo_awal) }}</h6>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <h6 class="mb-0">{{ number_format($shiftKasir->saldo_akhir) }}</h6>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Setor Uang -->
    <div class="modal fade" id="setorUang" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <h5 class="text-black">Jumlah Setor</h5>
                    <form action="/setoruang" method="post">
                        @csrf
                        <input type="hidden" class="form-control" name="metode_pembayaran" value="Cash">
                        <input type="hidden" name="total_pembayaran" id="total_pembayaran" min="0">
                        <input type="text" inputmode="numeric" class="form-control total-pembayaran" min="0">
                        <button class="mt-3 btn btn-dark" type="submit">Setor Uang</button>
                    </form>
                </div>
            </div>
        </div>
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
            $('.format-number-awal').on('input', function () {
                let input = $(this);
                let value = input.val().replace(/\D/g, ''); // hanya angka

                // Format ribuan pakai titik
                let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                input.val(formatted); // tampilkan ke input

                // Update hidden input (tanpa format)
                $('#saldo_awal_hidden').val(value);
            });

            $('.format-number-akhir').on('input', function () {
                let input = $(this);
                let value = input.val().replace(/\D/g, ''); // hanya angka

                // Format ribuan pakai titik
                let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                input.val(formatted); // tampilkan ke input

                // Update hidden input (tanpa format)
                $('#saldo_akhir_hidden').val(value);
            });

            $('.total-pembayaran').on('input', function () {
                let input = $(this);
                let value = input.val().replace(/\D/g, ''); // hanya angka

                // Format ribuan pakai titik
                let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                input.val(formatted); // tampilkan ke input

                // Update hidden input (tanpa format)
                $('#total_pembayaran').val(value);
            });
        });
    </script>
    {{-- @push('scripts')
    @endpush --}}
@endsection
