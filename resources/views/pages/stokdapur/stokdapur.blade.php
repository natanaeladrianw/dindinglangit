@extends('layouts.master')

@section('content')
    @if (Auth::user()->role == 'admin_gudang')
        <div class="card p-4">
            <h3 class="text-black fw-bold">Stok Dapur</h3>
            @if ($belumAdaStok->isEmpty())
                <a data-bs-toggle="modal" data-bs-target="#belumAdaStok" class="btn btn-dark d-flex text-light align-items-center mb-2 mt-2" style="width: fit-content">
                    <i class="fa-solid fa-plus me-2"></i>
                    Add Stok Awal
                </a>
            @else
                <a href="{{ route('stokdapur.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
                    <i class="fa-solid fa-plus me-2"></i>
                    Add Stok Awal
                </a>
            @endif
            
            <!-- Search and Filter Section -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" id="searchStokDapur" class="form-control" placeholder="Cari nama/kode bahan baku...">
                        <button class="btn btn-dark" type="button" onclick="searchStokDapur()">Cari</button>
                        <button class="btn btn-secondary" type="button" onclick="resetSearch()">Reset</button>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="showExpiringSoon">
                        <label class="form-check-label" for="showExpiringSoon">
                            Tampilkan yang mendekati kadaluwarsa
                        </label>
                    </div>
                </div>
            </div>
            
            <table class="table table-hover" id="stokDapur">
                <thead>
                <tr class="table-dark">
                    <th class="text-white" scope="col">No.</th>
                    <th class="text-white" scope="col">Kode</th>
                    <th class="text-white" scope="col">Bahan Baku</th>
                    <th class="text-white text-center" scope="col">Sisa Fisik</th>
                    <th class="text-white text-center" scope="col">Satuan Kecil</th>
                    <th class="text-white text-center" scope="col">Tanggal Kadaluarsa</th>
                    <th class="text-white text-center" scope="col">Kadaluarsa</th>
                    <th class="text-white text-center" scope="col">Aksi</th>
                </tr>
                </thead>
                <tbody>
                    @if ($stokDapurs->isEmpty())
                        <tr>
                            Tidak ada Stok Dapur.
                        </tr>
                    @else
                        @foreach ($stokDapurs as $stokDapur)
                            <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td>{{ $stokDapur->bahanBakus->kode }}</td>
                                <td>{{ $stokDapur->bahanBakus->nama }}</td>
                                <td class="text-center">{{ number_format($stokDapur->jumlah * $stokDapur->satuans->getKonversiKeTerkecil())  }}</td>
                                <td class="text-center">
                                    {{ $stokDapur->satuans?->getNamaSatuanTerkecil() }}
                                </td>
                                <td class="text-center">{{ $stokDapur->tanggal_exp ? Carbon\Carbon::parse($stokDapur->tanggal_exp)->translatedFormat('l, d F Y') : '-' }}</td>
                                <td>
                                    @if($stokDapur->mendekati_kadaluarsa > 0)
                                        <div class="text-center">
                                            <span class="badge bg-warning">
                                                Mendekati Kadaluwarsa
                                            </span>
                                        </div>
                                    @elseif ($stokDapur->sudah_kadaluarsa > 0)
                                        <div class="text-center">
                                            <span class="badge bg-danger">
                                                Sudah Kadaluwarsa
                                            </span>
                                        </div>
                                    @else
                                        <div class="text-center">
                                            -
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stokDapur->sudah_kadaluarsa > 0)
                                        <button class="text-black btn btn-secondary me-3" disabled onclick="showExpiredAlert()">
                                            <i class="fa-solid fa-plus"></i> Tambah Stok
                                        </button>
                                    @else
                                        <a class="text-black btn btn-warning me-3" href="/restokdapur/{{ $stokDapur->id }}">
                                            <i class="fa-solid fa-plus"></i> Tambah Stok
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        {{-- belumAdaStok Modal --}}
        <div class="modal fade" id="belumAdaStok" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Semua bahan baku telah tersedia di dapur!</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal-->
        @foreach ($stokDapurs as $stokDapur)
            <div class="modal fade" id="delete{{ $stokDapur->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <h5>Apakah anda yakin untuk menghapus jenis Stok Dapur "{{ $stokDapur->bahanBakus->nama }}"?</h5>
                            <div class="mt-4 d-flex justify-content-end">
                                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('stokdapur.destroy', $stokDapur->id) }}" method="post">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="ms-2 btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <script>
            var msg = '{{ Session::get('alert') }}';

            var exist = '{{ Session::has('alert') }}';

            if (exist) {
                alert(msg);
            }
        </script>
        
        <script>
            function showExpiredAlert() {
                alert('Bahan baku ini sudah kadaluwarsa dan tidak dapat ditambahkan stok!');
            }
        </script>
        
        <script>
            function showExpiredAlert() {
                alert('Bahan baku ini sudah kadaluwarsa dan tidak dapat ditambahkan stok!');
            }
        </script>
        
        <script>
            let allRows = [];
            let filteredRows = [];
            
            document.addEventListener('DOMContentLoaded', function() {
                const tbody = document.querySelector('#stokDapur tbody');
                allRows = Array.from(tbody.querySelectorAll('tr'));
                filteredRows = [...allRows];
                
                // Add event listeners
                document.getElementById('searchStokDapur').addEventListener('input', function() {
                    searchStokDapur();
                });
                
                document.getElementById('showExpiringSoon').addEventListener('change', function() {
                    searchStokDapur();
                });
            });
            
            function searchStokDapur() {
                const searchTerm = document.getElementById('searchStokDapur').value.toLowerCase();
                const showExpiring = document.getElementById('showExpiringSoon').checked;
                
                filteredRows = allRows.filter(row => {
                    const text = row.textContent.toLowerCase();
                    const hasSearchMatch = text.includes(searchTerm);
                    
                    if (showExpiring) {
                        // Check if row has expiration warning
                        const hasExpirationWarning = row.querySelector('.badge.bg-warning, .badge.bg-danger');
                        return hasSearchMatch && hasExpirationWarning;
                    }
                    
                    return hasSearchMatch;
                });
                
                renderTable();
            }
            
            function resetSearch() {
                document.getElementById('searchStokDapur').value = '';
                document.getElementById('showExpiringSoon').checked = false;
                filteredRows = [...allRows];
                renderTable();
            }
            
            function renderTable() {
                const tbody = document.querySelector('#stokDapur tbody');
                
                // Remove any existing "no data" rows first
                const existingNoDataRows = tbody.querySelectorAll('tr td[colspan]');
                existingNoDataRows.forEach(row => {
                    if (row.textContent.includes('Tidak ada data yang ditemukan')) {
                        row.parentElement.remove();
                    }
                });
                
                // Hide all rows
                allRows.forEach(row => row.style.display = 'none');
                
                // Show filtered rows
                filteredRows.forEach((row, index) => {
                    row.style.display = '';
                    // Update row number
                    const rowNumber = row.querySelector('th[scope="row"]');
                    if (rowNumber) {
                        rowNumber.textContent = index + 1;
                    }
                });
                
                // Show "no data" message if no results
                if (filteredRows.length === 0) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.innerHTML = '<td colspan="8" class="text-center">Tidak ada data yang ditemukan.</td>';
                    tbody.appendChild(noDataRow);
                }
            }
        </script>
    @else
        <div class="card p-4">
            <h3 class="text-black fw-bold">Stok Dapur</h3>
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark me-2">Filter</button>
                    <a href="{{ route('stokdapur.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
            <table class="table table-hover" id="stokDapur">
                <thead>
                <tr class="table-dark">
                    <th class="text-white" scope="col">No.</th>
                    <th class="text-white" scope="col">Kode</th>
                    <th class="text-white" scope="col">Bahan Baku</th>
                    <th class="text-white text-center" scope="col">Stok Dapur</th>
                    <th class="text-white text-center" scope="col">Satuan</th>
                </tr>
                </thead>
                <tbody>
                    @if ($stokDapurs->isEmpty())
                        <tr>
                            Tidak ada Stok Dapur.
                        </tr>
                    @else
                        @foreach ($stokDapurs as $stokDapur)
                            <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td>{{ $stokDapur->bahanBakus->kode }}</td>
                                <td>{{ $stokDapur->bahanBakus->nama }}</td>
                                <td class="text-center">{{ $stokDapur->jumlah == floor($stokDapur->jumlah) ? number_format($stokDapur->jumlah, 0, '.', ',') : number_format($stokDapur->jumlah, 1, '.', ',') }}</td>
                                <td class="text-center">
                                    {{ $stokDapur->satuans?->getNamaSatuanTerkecil() }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    @endif
@endsection
