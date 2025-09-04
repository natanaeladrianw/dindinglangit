@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Grup Bahan Baku</h3>
        <a href="{{ route('grupbahanbaku.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Grup Bahan Baku
        </a>
        <div class="mb-3">
            <input type="text" id="searchGrupBahanBaku" class="form-control" placeholder="Cari...">
        </div>
        <table class="table table-hover" id="bahanBaku">
            <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Nama</th>
                <th class="text-white" scope="col">Keterangan</th>
                <th class="text-white text-center" scope="col">Status Pengajuan</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($grupBahanBakus->isEmpty())
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada grup bahan baku.</td>
                    </tr>
                @else
                    @foreach ($grupBahanBakus as $bahanBaku)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $bahanBaku->nama }}</td>
                            <td>{{ $bahanBaku->keterangan ?? '-' }}</td>
                            <td class="text-center">
                                @if($bahanBaku->pengajuan)
                                    <span class="badge bg-success">Sudah Diajukan</span>
                                @else
                                    <span class="badge bg-warning">Belum Diajukan</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a class="btn btn-primary me-2" href="{{ route('grupbahanbaku.show', ['grupbahanbaku' => $bahanBaku->id]) }}">View</a>
                                <a class="text-black btn btn-warning me-2" href="{{ route('grupbahanbaku.edit', ['grupbahanbaku' => $bahanBaku->id]) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <form action="{{ route('grupbahanbaku.toggle-pengajuan', $bahanBaku->id) }}" method="POST" class="d-inline me-2">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn {{ $bahanBaku->pengajuan ? 'btn-secondary' : 'btn-success' }}">
                                        {{ $bahanBaku->pengajuan ? 'Batalkan Pengajuan' : 'Ajukan' }}
                                    </button>
                                </form>
                                <a class="text-black btn btn-danger" data-bs-target="#delete{{ $bahanBaku->id }}" data-bs-toggle="modal"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Delete Modal-->
    @foreach ($grupBahanBakus as $bahan)
        <div class="modal fade" id="delete{{ $bahan->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Apakah anda yakin untuk menghapus bahan baku "{{ $bahan->nama }}"?</h5>
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('grupbahanbaku.destroy', $bahan->id) }}" method="post">
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
            const input = document.getElementById('searchGrupBahanBaku');
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
