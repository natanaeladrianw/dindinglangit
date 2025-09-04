@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Add Resep</h3>
        <form action="{{ route('resep.store') }}" method="post">
            @csrf

            <!-- Menu -->
            <label for="">Menu</label>
            <select class="form-select mb-3" name="menu_id" required>
                <option value="" selected>Pilih</option>
                @foreach ($menus as $menu)
                    <option value="{{ $menu->id }}">{{ $menu->nama_item }}</option>
                @endforeach
            </select>

            <!-- Bahan Baku -->
            <label for="">Bahan Baku</label>
            <select class="form-select mb-3" name="grup_bahan_baku_ids[]" multiple id="grup_bahan_baku_ids">
                @foreach ($bahanBakus as $bahanBaku)
                    <option value="{{ $bahanBaku->id }}">{{ $bahanBaku->nama }}</option>
                @endforeach
            </select>

            <!-- Dinamis: Jumlah & Satuan -->
            <div id="dynamic_inputs_container"></div>

            <div class="text-end mt-3">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>

    </div>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk dropdown bahan baku
            $('#grup_bahan_baku_ids').select2({
                placeholder: "Pilih", // Teks placeholder
                allowClear: true // Memungkinkan menghapus pilihan
            });

            $('#satuan_ids').select2({
                placeholder: "Pilih",
                allowClear: true
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#grup_bahan_baku_ids').select2({
                placeholder: "Pilih bahan baku",
                allowClear: true
            });

            const bahanBakus = @json($bahanBakus->keyBy('id'));
            const satuans = @json($satuans);
            const container = $('#dynamic_inputs_container');

            function renderInputs(selectedIds) {
                container.empty();

                selectedIds.forEach(id => {
                    const bahan = bahanBakus[id];
                    const satuanOptions = satuans.map(s =>
                        `<option value="${s.id}">${s.nama}</option>`
                    ).join('');

                    const html = `
                        <div class="mt-3 mb-3 border rounded p-3" data-id="${id}">
                            <label><strong>${bahan.nama}</strong></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Jumlah</label>
                                    <input type="number" name="jml_bahans[${id}]" class="form-control" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Satuan</label>
                                    <select name="satuan_ids[${id}]" class="form-select" required>
                                        <option value="">Pilih satuan</option>
                                        ${satuanOptions}
                                    </select>
                                </div>
                            </div>
                        </div>
                    `;

                    container.append(html);
                });
            }

            $('#grup_bahan_baku_ids').on('change', function () {
                const selected = $(this).val();
                renderInputs(selected || []);
            });

            // Trigger on page load (old form support)
            $('#grup_bahan_baku_ids').trigger('change');
        });
    </script>
@endsection
