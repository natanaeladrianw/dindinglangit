@extends('layouts.master')

@section('content')
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .menu-row {
            background-color: #e6f7ff; /* Warna latar belakang untuk baris menu */
            font-weight: bold;
        }
        .pagination {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }
        .pagination .page-link {
            border: 1px solid #000;
            padding: 4px 10px;
            border-radius: 4px;
            background: #fff;
            cursor: pointer;
            color: #000;
        }
        .pagination .page-link:focus {
            outline: none;
            box-shadow: none;
        }
        .pagination .active {
            background: #fff;
            color: #000;
            border-color: #000;
        }
    </style>
    <div class="card p-4">
        {{-- @dd($groupedReseps) --}}
        <h3 class="text-black fw-bold">Resep</h3>
        <a href="{{ route('resep.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Resep
        </a>
        <table class="table table-hover" id="resep">
            <thead>
            <tr class="table-dark">
                <th class="text-white text-center" scope="col">No.</th>
                <th class="text-white" scope="col">Menu</th>
                <th class="text-white" scope="col">Bahan-bahan</th>
                <th class="text-white text-center" scope="col">Jumlah</th>
                <th class="text-white text-center" scope="col">Satuan</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($groupedReseps->isEmpty())
                    <tr>
                        Tidak ada resep.
                    </tr>
                @else
                    @php
                        $no = 1;
                    @endphp
                    @foreach ($groupedReseps as $menuId => $bahanBakuMenus)
                        @php
                            // Ambil detail menu dari item pertama di grup (karena semua di grup ini memiliki menu_id yang sama)
                            $menu = $bahanBakuMenus->first()->menus;
                            $firstRow = true; // Flag untuk menentukan baris pertama dalam grup
                        @endphp

                        @foreach ($bahanBakuMenus as $bahanBakuMenu)
                            <tr>
                                @if ($firstRow)
                                    {{-- Tampilkan Nama Menu hanya di baris pertama grup --}}
                                    <td rowspan="{{ $bahanBakuMenus->count() }}" class="menu-row text-center">
                                        {{ $no++ }}
                                    </td>
                                    <td rowspan="{{ $bahanBakuMenus->count() }}" class="menu-row">
                                        {{ $menu->nama_item }}
                                    </td>
                                @endif
                                {{-- Tampilkan detail bahan baku --}}
                                <td>{{ $bahanBakuMenu->grupBahanBaku->nama }}</td>
                                <td class="text-center">{{ $bahanBakuMenu->jml_bahan }}</td>
                                <td class="text-center">{{ $bahanBakuMenu->satuans->nama }}</td>
                                @if ($firstRow)
                                    {{-- Kolom Aksi Baru untuk Menu --}}
                                    <td rowspan="{{ $bahanBakuMenus->count() }}" class="menu-row text-center" style="width: 20%">
                                        <a href="{{ route('resep.edit_menu', $menu->id) }}" class="text-black btn btn-warning me-3"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                        <button type="button" class="text-black btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteMenuModal{{ $menu->id }}">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </td>

                                    @php $firstRow = false; @endphp
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small id="resepPageInfo"></small>
            </div>
            <div id="resepPagination" class="pagination"></div>
        </div>
    </div>

    <!-- Delete Modal-->
    @foreach ($menus as $menu)
        <div class="modal fade" id="deleteMenuModal{{ $menu->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Apakah anda yakin untuk menghapus resep "{{ $menu->nama_item }}"?</h5>
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <form action="/resep/{{ $menu->id }}}/destroy-menu" method="post">
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
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('resep');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const pagination = document.getElementById('resepPagination');
            const pageInfo = document.getElementById('resepPageInfo');
            const pageSize = 10; // groups per page

            // Build groups based on rows where a group starts when a row contains an element with class 'menu-row'
            const groups = [];
            let currentGroup = [];
            rows.forEach((row) => {
                const isGroupStart = row.querySelector('.menu-row') !== null;
                if (isGroupStart && currentGroup.length > 0) {
                    groups.push(currentGroup);
                    currentGroup = [];
                }
                currentGroup.push(row);
            });
            if (currentGroup.length > 0) groups.push(currentGroup);

            let currentPage = 1;
            const totalPages = Math.max(1, Math.ceil(groups.length / pageSize));

            function renderPage(page) {
                currentPage = Math.min(Math.max(1, page), totalPages);
                // Hide all rows
                rows.forEach(r => r.style.display = 'none');
                // Show rows for current page by groups
                const start = (currentPage - 1) * pageSize;
                const end = Math.min(start + pageSize, groups.length);
                for (let i = start; i < end; i++) {
                    groups[i].forEach(r => r.style.display = '');
                }
                renderControls();
                pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages}`;
            }

            function renderControls() {
                pagination.innerHTML = '';
                const createBtn = (label, page, disabled = false, active = false) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'page-link' + (active ? ' active' : '');
                    btn.textContent = label;
                    btn.disabled = disabled;
                    btn.addEventListener('click', () => renderPage(page));
                    return btn;
                };
                const createTextLink = (text, page, disabled) => {
                    const span = document.createElement('span');
                    span.textContent = text;
                    span.style.color = disabled ? '#6c757d' : '#000';
                    span.style.margin = '0 6px';
                    span.style.cursor = disabled ? 'default' : 'pointer';
                    if (!disabled) {
                        span.addEventListener('click', () => renderPage(page));
                    }
                    return span;
                };
                pagination.appendChild(createTextLink('Prev', currentPage - 1, currentPage === 1));
                for (let i = 1; i <= totalPages; i++) {
                    pagination.appendChild(createBtn(String(i), i, false, i === currentPage));
                }
                pagination.appendChild(createTextLink('Next', currentPage + 1, currentPage === totalPages));
            }

            renderPage(1);
        });
    </script>
@endsection
