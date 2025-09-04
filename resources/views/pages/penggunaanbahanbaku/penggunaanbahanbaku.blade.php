@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <h3 class="text-black fw-bold">Penggunaan Bahan Baku</h3>
        <a href="{{ route('penggunaanbahanbaku.create') }}" class="btn btn-dark d-flex align-items-center mb-2 mt-2" style="width: fit-content">
            <i class="fa-solid fa-plus me-2"></i>
            Add Penggunaan Bahan Baku
        </a>
        <!-- Form Filter -->
        <form method="GET" action="{{ route('penggunaanbahanbaku.index') }}">
            {{-- @csrf --}}
            <div class="row mb-3">
                <div class="col-md-2">
                    <label>Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control"
                        value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control"
                        value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label>Urutkan</label>
                    <select name="sort" class="form-select">
                        <option value="ASC" {{ request('sort') == 'ASC' ? 'selected' : '' }}>
                            Terlama
                        </option>
                        <option value="DESC" {{ request('sort') == 'DESC' ? 'selected' : '' }}>
                            Terbaru
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Cari Bahan Baku</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama bahan...">
                </div>

                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-dark">Filter</button>
                    <a href="{{ route('penggunaanbahanbaku.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
        <table class="table table-hover" id="penggunaanbahanbaku">
            <thead>
            <tr class="table-dark">
                <th class="text-white text-center" scope="col">No.</th>
                <th class="text-white" scope="col">Kode</th>
                <th class="text-white" scope="col">Bahan Baku</th>
                {{-- <th class="text-white text-center" scope="col">Satuan Kecil</th> --}}
                <th class="text-white" scope="col">Jumlah</th>
                <th class="text-white" scope="col">Sisa Fisik</th>
                <th class="text-white" scope="col">Tanggal</th>
                <th class="text-white text-center" scope="col">Keterangan</th>
                <th class="text-white text-center" scope="col">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @if ($penggunaanBahanBakus->isEmpty())
                    <tr>
                        Tidak ada Penggunaan Bahan Baku.
                    </tr>
                @else
                    @foreach ($penggunaanBahanBakus as $penggunaanBahanBaku)
                        {{-- @if ($penggunaanBahanBaku->id == 36)
                        @endif --}}
                        @php
                            $satuanDapur = \App\Models\Satuan::find($penggunaanBahanBaku->bahanBakus->stokDapurs->first()?->satuan_id);
                            // dd($satuanDapur);
                        @endphp
                        <tr>
                            <th scope="row" class="text-center">{{ $loop->iteration }}</th>
                            <td>{{ $penggunaanBahanBaku->bahanBakus->kode }}</td>
                            <td>{{ $penggunaanBahanBaku->bahanBakus->nama }}</td>
                            <td class="">{{ $penggunaanBahanBaku->jumlah_pakai == floor($penggunaanBahanBaku->jumlah_pakai) ? number_format($penggunaanBahanBaku->jumlah_pakai, 0, '.', ',') : number_format($penggunaanBahanBaku->jumlah_pakai, 1, '.', ',') }} {{ $penggunaanBahanBaku->satuans->nama }}</td>
                            <td class="">{{ $penggunaanBahanBaku->sisa_fisik == floor($penggunaanBahanBaku->sisa_fisik) ? number_format($penggunaanBahanBaku->sisa_fisik, 0, '.', ',') : number_format($penggunaanBahanBaku->sisa_fisik, 1, '.', ',') }} {{ $satuanDapur->nama }}</td>
                            <td class="">{{ Carbon\Carbon::parse($penggunaanBahanBaku->created_at)->translatedFormat('l, d F Y') }}</td>
                            <td class="text-center"><small>{{ $penggunaanBahanBaku->keterangan ?? '-' }}</small></td>
                            <td class="text-center">
                                <a class="text-black btn btn-warning me-3" href="{{ route('penggunaanbahanbaku.edit', ['penggunaanbahanbaku' => $penggunaanBahanBaku->id]) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a class="text-black btn btn-danger" data-bs-target="#delete{{ $penggunaanBahanBaku->id }}" data-bs-toggle="modal"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <div class="mt-3">
            {{ $penggunaanBahanBakus->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Delete Modal-->
    @foreach ($penggunaanBahanBakus as $penggunaanBahanBaku)
        <div class="modal fade" id="delete{{ $penggunaanBahanBaku->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Apakah anda yakin untuk menghapus jenis Penggunaan Bahan Baku "{{ $penggunaanBahanBaku->bahanBakus->nama }}"?</h5>
                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('penggunaanbahanbaku.destroy', $penggunaanBahanBaku->id) }}" method="post">
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
