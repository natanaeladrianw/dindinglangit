@extends('layouts.master')

@section('content')
<div class="card p-4">
    <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
        <i class="fa-solid fa-arrow-left me-2"></i> Back
    </a>
    <h3 class="text-black fw-bold">Edit Resep Menu: {{ $menu->nama_item }}</h3>

    <form action="{{ route('resep.update_menu', $menu->id) }}" method="post">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="menu_display" class="form-label">Menu</label>
            <input type="text" class="form-control" value="{{ $menu->nama_item }}" disabled>
            <input type="hidden" name="menu_id" value="{{ $menu->id }}">
        </div>

        <div class="mb-3">
            <label for="grup_bahan_baku_ids" class="form-label">Grup Bahan Baku</label>
            <select class="form-select" name="grup_bahan_baku_ids[]" multiple id="grup_bahan_baku_ids">
                @foreach ($bahanBakus as $bahan)
                    <option value="{{ $bahan->id }}"
                        @if ($menu->bahanBakuMenus->contains('grup_bahan_baku_id', $bahan->id))
                            selected
                        @endif>
                        {{ $bahan->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div id="dynamic_inputs_container"></div>

        <div class="text-end mt-3">
            <button type="submit" class="btn btn-dark">Update Resep</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function () {
        $('#grup_bahan_baku_ids').select2({
            placeholder: "Pilih bahan baku",
            allowClear: true
        });

        const bahanBakus = @json($bahanBakus->keyBy('id'));
        const satuans = @json($satuans);
        const existing = @json($menu->bahanBakuMenus->keyBy('grup_bahan_baku_id'));
        const container = $('#dynamic_inputs_container');

        function renderInputs(selected) {
            container.empty();
            selected.forEach(id => {
                const bahan = bahanBakus[id];
                const existingData = existing[id] || {};
                const jumlah = existingData.jml_bahan || '';
                const selectedSatuan = existingData.satuan_id || '';

                const satuanOptions = satuans.map(s => {
                    const selected = s.id == selectedSatuan ? 'selected' : '';
                    return `<option value="${s.id}" ${selected}>${s.nama}</option>`;
                }).join('');

                const html = `
                    <div class="border p-3 mb-3" data-id="${id}">
                        <label><strong>${bahan.nama}</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Jumlah</label>
                                <input type="text" inputMode="numeric" name="jml_bahans[${id}]" class="form-control" min="0" value="${jumlah}" required>
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
            const selected = $(this).val() || [];
            renderInputs(selected);
        });

        $('#grup_bahan_baku_ids').trigger('change');
    });
</script>
@endsection
