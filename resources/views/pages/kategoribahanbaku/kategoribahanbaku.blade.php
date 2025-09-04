@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Kategori Bahan Baku</h3>
        <a href="{{ route('kategoribahanbaku.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Kategori Bahan Baku
        </a>
        <div class="mb-3">
            <input type="text" id="searchKategoriBahanBaku" class="form-control" placeholder="Cari...">
        </div>
        <table class="table table-hover" id="kategoriBahanBaku">
            <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Jenis Bahan Baku</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($kategoriBahanBakus->isEmpty())
                    <tr>
                        Tidak ada kategori bahan baku.
                    </tr>
                @else
                    @foreach ($kategoriBahanBakus as $kategoriBahanBaku)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $kategoriBahanBaku->jenis_bahan_baku }}</td>
                            <td class="text-center">
                                <a class="text-black btn btn-warning me-3" href="{{ route('kategoribahanbaku.edit', ['kategoribahanbaku' => $kategoriBahanBaku->id]) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a class="text-black btn btn-danger" data-bs-target="#delete{{ $kategoriBahanBaku->id }}" data-bs-toggle="modal"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Delete Modal-->
    @foreach ($kategoriBahanBakus as $kategori)
        <div class="modal fade" id="delete{{ $kategori->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Apakah anda yakin untuk menghapus kategori bahan baku "{{ $kategori->jenis_bahan_baku }}"?</h5>
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('kategoribahanbaku.destroy', $kategori->id) }}" method="post">
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
            const input = document.getElementById('searchKategoriBahanBaku');
            const table = document.getElementById('kategoriBahanBaku');
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            input.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                rows.forEach(function(row) {
                    const match = row.textContent.toLowerCase().includes(q);
                    row.style.display = match ? '' : 'none';
                });
            });
        });
    </script>
@endsection
