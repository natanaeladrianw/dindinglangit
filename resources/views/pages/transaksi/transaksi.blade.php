@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Transaksi</h3>
        {{-- <a href="{{ route('transaksi.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Transaksi
        </a> --}}
        <form id="bulkForm" action="{{ route('transaksi.bulk_update_status') }}" method="POST" class="mb-2 d-flex align-items-center gap-2">
            @csrf
            @method('PATCH')
            <select name="status" class="form-select" style="max-width: 200px;">
                <option value="0">Antrian</option>
                <option value="1">Selesai</option>
            </select>
            <button type="submit" class="btn btn-dark">Ubah Status Terpilih</button>
        </form>

        <table class="table table-hover" id="transaksi">
            <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col"><input type="checkbox" id="checkAll"></th>
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Item</th>
                <th class="text-white text-center" scope="col">Total pesanan</th>
                <th class="text-white text-center" scope="col">Status</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($transaksis->isEmpty())
                    <tr>
                        Tidak ada transaksi.
                    </tr>
                @else
                    @foreach ($transaksis as $transaksi)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $transaksi->id }}" class="row-check" form="bulkForm"></td>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $transaksi->menus->pluck('nama_item')->join(', ') }}</td>
                            <td class="text-center">
                                {{
                                    $transaksi->menus->sum(function ($menu) {
                                        return $menu->pivot->jumlah_pesanan;
                                    })
                                }}
                            </td>
                            <td class="text-center">
                                @if($transaksi->status == 1)
                                    <span class="badge bg-success">Selesai</span>
                                @else
                                    <span class="badge bg-warning">Antrian</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a class="text-black btn btn-info me-3" href="{{ route('transaksi.show', ['transaksi' => $transaksi->id]) }}"><i class="fa-solid fa-info"></i> Info</a>
                                <a class="text-black btn btn-warning me-3" href="{{ route('transaksi.edit', ['transaksi' => $transaksi->id]) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                {{-- <a class="text-black btn btn-danger" data-bs-target="#delete{{ $transaksi->id }}" data-bs-toggle="modal"><i class="fa-solid fa-trash"></i> Delete</a> --}}
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <div class="mt-3">
            {{ $transaksis->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
        document.getElementById('checkAll')?.addEventListener('change', function() {
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        });
    </script>

    <!-- Delete Modal-->
    @foreach ($transaksis as $transaksi)
        <div class="modal fade" id="delete{{ $transaksi->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Apakah anda yakin untuk menghapus transaksi "{{ $transaksi->menus->pluck('nama_item')->join(', ') }}"?</h5>
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('transaksi.destroy', $transaksi->id) }}" method="post">
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
@endsection
