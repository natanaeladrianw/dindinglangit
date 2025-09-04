@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Menu</h3>
        <a href="{{ route('menu.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Menu
        </a>
        <div class="mb-3">
            <input type="text" id="searchMenu" class="form-control" placeholder="Cari...">
        </div>
        <table class="table table-hover" id="menu">
            <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Kategori</th>
                <th class="text-white" scope="col">Nama</th>
                <th class="text-white" scope="col">Harga</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($menus->isEmpty())
                    <tr>
                        Tidak ada menu.
                    </tr>
                @else
                    @foreach ($menus as $menu)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $menu->kategoriMenus->jenis_kategori_menu }}</td>
                            <td>{{ $menu->nama_item }}</td>
                            <td>Rp{{ number_format($menu->harga) }}</td>
                            <td class="text-center">
                                <a class="text-black btn btn-warning me-3" href="{{ route('menu.edit', ['menu' => $menu->id]) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a class="text-black btn btn-danger" data-bs-target="#delete{{ $menu->id }}" data-bs-toggle="modal"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small id="menuPageInfo"></small>
            </div>
            <div id="menuPagination" class="pagination"></div>
        </div>
    </div>

    <!-- Delete Modal-->
    @foreach ($menus as $menu)
        <div class="modal fade" id="delete{{ $menu->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Apakah anda yakin untuk menghapus menu "{{ $menu->nama_item }}"?</h5>
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('menu.destroy', $menu->id) }}" method="post">
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
            const searchInput = document.getElementById('searchMenu');
            const table = document.getElementById('menu');
            const tbody = table.querySelector('tbody');
            const allRows = Array.from(tbody.querySelectorAll('tr'));
            const pagination = document.getElementById('menuPagination');
            const pageInfo = document.getElementById('menuPageInfo');
            const pageSize = 10;
            let filteredRows = [...allRows];
            let currentPage = 1;

            function applyFilter() {
                const q = searchInput.value.toLowerCase();
                filteredRows = allRows.filter(r => r.textContent.toLowerCase().includes(q));
                currentPage = 1;
                render();
            }

            function render() {
                allRows.forEach(r => r.style.display = 'none');
                const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
                currentPage = Math.min(currentPage, totalPages);
                const start = (currentPage - 1) * pageSize;
                const end = Math.min(start + pageSize, filteredRows.length);
                for (let i = start; i < end; i++) {
                    filteredRows[i].style.display = '';
                }
                renderControls(totalPages);
                pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages}`;
            }

            function renderControls(totalPages) {
                pagination.innerHTML = '';
                const createBtn = (label, page, disabled = false, active = false) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'page-link' + (active ? ' active' : '');
                    btn.textContent = label;
                    btn.style.border = '1px solid #000';
                    btn.style.color = '#000';
                    btn.style.background = '#fff';
                    btn.disabled = disabled;
                    btn.addEventListener('click', () => { currentPage = page; render(); });
                    return btn;
                };
                const createTextLink = (text, page, disabled) => {
                    const span = document.createElement('span');
                    span.textContent = text;
                    span.style.color = disabled ? '#6c757d' : '#000';
                    span.style.margin = '0 6px';
                    span.style.cursor = disabled ? 'default' : 'pointer';
                    if (!disabled) {
                        span.addEventListener('click', () => { currentPage = page; render(); });
                    }
                    return span;
                };
                pagination.appendChild(createTextLink('Prev', currentPage - 1, currentPage === 1));
                for (let i = 1; i <= totalPages; i++) {
                    pagination.appendChild(createBtn(String(i), i, false, i === currentPage));
                }
                pagination.appendChild(createTextLink('Next', currentPage + 1, currentPage === totalPages));
            }

            searchInput.addEventListener('input', applyFilter);
            applyFilter();
        });
    </script>
@endsection
