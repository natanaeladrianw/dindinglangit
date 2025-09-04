@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Satuan</h3>
        <a href="{{ route('satuan.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Satuan
        </a>
        <table class="table table-hover" id="satuan">
            <thead>
            <tr class="table-dark">
                <th class="text-white text-center" scope="col">No.</th>
                <th class="text-white text-center" scope="col">Satuan</th>
                <th class="text-white text-center" scope="col">Satuan Kecil</th>
                <th class="text-white text-center" scope="col">Nilai Satuan Kecil</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($satuans->isEmpty())
                    <tr>
                        Tidak ada satuan.
                    </tr>
                @else
                    @foreach ($satuans as $satuan)
                        <tr>
                            <th scope="row" class="text-center">{{ $loop->iteration }}</th>
                            <td class="text-center">{{ $satuan->nama }}</td>
                            <td class="text-center">{{ $satuan->satuanKecil ? $satuan->satuanKecil->nama : '-' }}</td>
                            <td class="text-center">{{ $satuan->nilai == floor($satuan->nilai) ? number_format($satuan->nilai, 0, '.', ',') : number_format($satuan->nilai, 1, '.', ',') }}</td>
                            <td class="text-center">
                                <a class="text-black btn btn-warning me-3" href="{{ route('satuan.edit', ['satuan' => $satuan->id]) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a class="text-black btn btn-danger" data-bs-target="#delete{{ $satuan->id }}" data-bs-toggle="modal"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Delete Modal-->
    @foreach ($satuans as $satuan)
        <div class="modal fade" id="delete{{ $satuan->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Apakah anda yakin untuk menghapus satuan "{{ $satuan->nama }}"?</h5>
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('satuan.destroy', $satuan->id) }}" method="post">
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
