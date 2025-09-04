@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Stok Gudang</h3>
        <a href="{{ route('notabeli.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Stok Gudang
        </a>
        
        <!-- Search and Filter Section -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" id="searchStokGudang" class="form-control" placeholder="Cari nama/kode bahan baku...">
                    <button class="btn btn-dark" type="button" onclick="searchStokGudang()">Cari</button>
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
        
        <table class="table table-hover" id="stokGudang">
            <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Kode</th>
                <th class="text-white" scope="col">Bahan Baku</th>
                <th class="text-white text-center" scope="col">Stok Gudang</th>
                <th class="text-white text-center" scope="col">Satuan</th>
                <th class="text-white text-center" scope="col">Tanggal Masuk Terakhir</th>
                <th class="text-white text-center" scope="col">Tanggal Kadaluwarsa</th>
                <th class="text-white text-center" scope="col">Segera Gunakan Bahan!</th>
            </tr>
            </thead>
            <tbody>
                @if ($stokGudangs->isEmpty())
                    <tr>
                        Tidak ada Stok Gudang.
                    </tr>
                @else
                    @foreach ($stokGudangs as $stokGudang)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $stokGudang->bahanBakus->kode }}</td>
                            <td>{{ $stokGudang->bahanBakus->nama }}</td>
                            <td class="text-center">{{ $stokGudang->jumlah == floor($stokGudang->jumlah) ? number_format($stokGudang->jumlah, 0, '.', ',') : number_format($stokGudang->jumlah, 1, '.', ',') }}</td>
                            <td class="text-center">{{ $stokGudang->satuans->nama }}</td>
                            <td class="text-center">
                                @if($stokGudang->tanggal_masuk && $stokGudang->tanggal_masuk->created_at)
                                    {{ Carbon\Carbon::parse($stokGudang->tanggal_masuk->created_at)->translatedFormat('l, d F Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">{{ $stokGudang->tanggal_exp ? Carbon\Carbon::parse($stokGudang->tanggal_exp)->translatedFormat('l, d F Y') : '-' }}</td>
                            <td>
                                @if($stokGudang->mendekati_kadaluarsa > 0)
                                    <div class="text-center">
                                        <span class="badge bg-warning">
                                            Mendekati Kadaluwarsa
                                        </span>
                                    </div>
                                @elseif ($stokGudang->sudah_kadaluarsa > 0)
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
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <script>
        var msg = '{{ Session::get('alert') }}';

        var exist = '{{ Session::has('alert') }}';

        if (exist) {
            alert(msg);
        }
    </script>
    
    <script>
        let allRows = [];
        let filteredRows = [];
        
        document.addEventListener('DOMContentLoaded', function() {
            const tbody = document.querySelector('#stokGudang tbody');
            allRows = Array.from(tbody.querySelectorAll('tr'));
            filteredRows = [...allRows];
            
            // Add event listeners
            document.getElementById('searchStokGudang').addEventListener('input', function() {
                searchStokGudang();
            });
            
            document.getElementById('showExpiringSoon').addEventListener('change', function() {
                searchStokGudang();
            });
        });
        
        function searchStokGudang() {
            const searchTerm = document.getElementById('searchStokGudang').value.toLowerCase();
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
            document.getElementById('searchStokGudang').value = '';
            document.getElementById('showExpiringSoon').checked = false;
            filteredRows = [...allRows];
            renderTable();
        }
        
        function renderTable() {
            const tbody = document.querySelector('#stokGudang tbody');
            
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
@endsection
