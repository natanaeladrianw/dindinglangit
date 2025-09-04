@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Nota Beli</h3>
        <a href="{{ route('notabeli.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Nota Beli
        </a>
        <table class="table table-hover" id="notabeli">
            <thead>
            <tr class="table-dark">
                <th class="text-white" scope="col">No.</th>
                <th class="text-white" scope="col">Kode</th>
                <th class="text-white" scope="col">Bahan Baku</th>
                <th class="text-white text-center" scope="col">Jumlah</th>
                <th class="text-white text-center" scope="col">Satuan</th>
                <th class="text-white" scope="col">Supplier</th>
                <th class="text-white text-center" scope="col">Harga</th>
                <th class="text-white text-center" scope="col">Tanggal Transaksi</th>
                <th class="text-white text-center" scope="col">Tanggal Kadaluwarsa</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($notaBelis->isEmpty())
                    <tr>
                        Tidak ada Nota Beli.
                    </tr>
                @else
                    @foreach ($notaBelis as $pembelian)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $pembelian->bahanBakus->kode }}</td>
                            <td>{{ $pembelian->bahanBakus->nama }}</td>
                            <td class="text-center">{{ $pembelian->jumlah == floor($pembelian->jumlah) ? number_format($pembelian->jumlah, 0, '.', ',') : number_format($pembelian->jumlah, 1, '.', ',') }}</td>
                            <td class="text-center">
                                {{
                                    $pembelian->bahanBakus->stokGudangs->first()->satuans?->reference_satuan_id
                                        ? $pembelian->bahanBakus->stokGudangs->first()->satuans->nama
                                        : $pembelian->bahanBakus->stokGudangs->first()->satuans->satuanBesar->first()->nama
                                }}
                            </td>
                            <td>{{ $pembelian->notaBelis->suppliers->nama }}</td>
                            <td class="text-center">{{ number_format($pembelian->harga) }}</td>
                            <td class="text-center">{{ Carbon\Carbon::parse($pembelian->notaBelis->tanggal_transaksi)->format('d-m-Y') }}</td>
                            <td class="text-center">{{ $pembelian->tgl_exp ? Carbon\Carbon::parse($pembelian->tgl_exp)->translatedFormat('l, d F Y') : '-' }}</td>
                            <td class="text-center">
                                <a class="text-black btn btn-warning me-3" href="{{ route('notabeli.edit', ['notabeli' => $pembelian->id]) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a class="text-black btn btn-danger" data-bs-target="#delete{{ $pembelian->id }}" data-bs-toggle="modal"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <div class="mt-3">
            {{ $notaBelis->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Delete Modal-->
    @foreach ($notaBelis as $pembelian)
        <div class="modal fade" id="delete{{ $pembelian->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Apakah anda yakin untuk menghapus Nota Beli "{{ $pembelian->bahanBakus->nama }}" di Supplier "{{ $pembelian->notaBelis->suppliers->nama }}" pada tanggal {{ Carbon\Carbon::parse($pembelian->notaBelis->tanggal_transaksi)->format('d-m-Y') }}?</h5>
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('notabeli.destroy', $pembelian->id) }}" method="post">
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
