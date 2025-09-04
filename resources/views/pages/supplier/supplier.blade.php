@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Supplier</h3>
        <a href="{{ route('supplier.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Supplier
        </a>
        <div class="mb-3">
            <input type="text" id="searchSupplier" class="form-control" placeholder="Cari...">
        </div>
        <table class="table table-hover" id="supplier">
            <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Nama</th>
                <th class="text-white" scope="col">Alamat</th>
                <th class="text-white" scope="col">Kontak</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($suppliers->isEmpty())
                    <tr>
                        Tidak ada supplier.
                    </tr>
                @else
                    @foreach ($suppliers as $supplier)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $supplier->nama }}</td>
                            <td style="max-width: 300px !important; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">{{ $supplier->alamat }}</td>
                            <td>{{ $supplier->kontak }}</td>
                            <td class="text-center">
                                <a class="text-black btn btn-warning me-3" href="{{ route('supplier.edit', ['supplier' => $supplier->id]) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a class="text-black btn btn-danger" data-bs-target="#delete{{ $supplier->id }}" data-bs-toggle="modal"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Delete Modal-->
    @foreach ($suppliers as $supplier)
        <div class="modal fade" id="delete{{ $supplier->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Apakah anda yakin untuk menghapus supplier "{{ $supplier->nama }}"?</h5>
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('supplier.destroy', $supplier->id) }}" method="post">
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
            const input = document.getElementById('searchSupplier');
            const table = document.getElementById('supplier');
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
