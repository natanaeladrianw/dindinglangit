@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Bahan Baku</h3>
        <a href="{{ route('bahanbaku.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Bahan Baku
        </a>
        <div class="mb-3">
            <input type="text" id="searchBahanBaku" class="form-control" placeholder="Cari...">
        </div>
        <table class="table table-hover" id="bahanBaku">
            <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Kode</th>
                <th class="text-white" scope="col">Kategori</th>
                <th class="text-white" scope="col">Nama</th>
                <th class="text-white" scope="col">Grup Bahan Baku</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($bahanBakus->isEmpty())
                    <tr>
                        Tidak ada bahan baku.
                    </tr>
                @else
                    @foreach ($bahanBakus as $bahanBaku)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $bahanBaku->kode }}</td>
                            <td>{{ $bahanBaku->kategoriBahanBakus->jenis_bahan_baku }}</td>
                            <td>{{ $bahanBaku->nama }}</td>
                            <td>{{ $bahanBaku->grupBahanBaku->nama }}</td>
                            <td class="text-center">
                                <a class="text-black btn btn-warning me-3" href="{{ route('bahanbaku.edit', ['bahanbaku' => $bahanBaku->id]) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a class="text-black btn btn-danger" data-bs-target="#delete{{ $bahanBaku->id }}" data-bs-toggle="modal"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Delete Modal-->
    @foreach ($bahanBakus as $bahan)
        <div class="modal fade" id="delete{{ $bahan->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Apakah anda yakin untuk menghapus bahan baku "{{ $bahan->nama }}"?</h5>
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('bahanbaku.destroy', $bahan->id) }}" method="post">
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
            const input = document.getElementById('searchBahanBaku');
            const table = document.getElementById('bahanBaku');
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
