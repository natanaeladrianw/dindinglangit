@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Add Bahan Baku</h3>
        <form action="{{ route('bahanbaku.store') }}" method="post" id="form-bahanbaku">
            @csrf
            <div id="bahanbaku-container">
                <div class="bahanbaku-group mb-4 border-bottom pb-3">
                    <label>Grup Bahan Baku</label>
                    <select class="form-select mb-3" name="grup_bahan_baku_id[]">
                        <option value="" selected>Pilih</option>
                        @foreach ($grupBahanBakus as $grupBahanBaku)
                            <option value="{{ $grupBahanBaku->id }}">{{ $grupBahanBaku->nama }}</option>
                        @endforeach
                    </select>

                    <label>Kategori</label>
                    <select class="form-select mb-3" name="kategori_bahan_baku_id[]">
                        <option value="" selected>Pilih</option>
                        @foreach ($kategori_bahan_bakus as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->jenis_bahan_baku }}</option>
                        @endforeach
                    </select>

                    <label>Kode</label>
                    <input type="text" name="kode[]" class="form-control mb-3 kode-input" onblur="checkDuplicateKode(this)">
                    <div class="invalid-feedback kode-error"></div>

                    <label>Nama</label>
                    <input type="text" name="nama[]" class="form-control mb-3">
                </div>
            </div>

            <button type="button" class="btn btn-secondary mb-3" id="add-more">
                <i class="fa-solid fa-plus me-2"></i>Tambahkan Bahan Baku Lainnya
            </button>

            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>

    <script>
        // Array to store existing kodes (will be populated from server)
        let existingKodes = [];
        
        // Get existing kodes from server
        fetch('{{ route("bahanbaku.index") }}')
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const kodeElements = doc.querySelectorAll('td:nth-child(2)'); // Assuming kode is in 2nd column
                existingKodes = Array.from(kodeElements).map(el => el.textContent.trim());
            })
            .catch(error => console.error('Error loading existing kodes:', error));

        function checkDuplicateKode(input) {
            const kode = input.value.trim();
            const errorDiv = input.parentNode.querySelector('.kode-error');
            
            if (kode === '') {
                input.classList.remove('is-invalid');
                errorDiv.textContent = '';
                return;
            }
            
            if (existingKodes.includes(kode)) {
                input.classList.add('is-invalid');
                errorDiv.textContent = 'Kode ini sudah ada!';
                return false;
            } else {
                input.classList.remove('is-invalid');
                errorDiv.textContent = '';
                return true;
            }
        }

        function validateAllKodes() {
            const kodeInputs = document.querySelectorAll('.kode-input');
            const duplicates = [];
            
            kodeInputs.forEach(input => {
                const kode = input.value.trim();
                if (kode !== '' && existingKodes.includes(kode)) {
                    duplicates.push(kode);
                }
            });
            
            return duplicates;
        }

        document.getElementById('form-bahanbaku').addEventListener('submit', function(e) {
            const duplicates = validateAllKodes();
            
            if (duplicates.length > 0) {
                e.preventDefault();
                alert('Kode bahan baku berikut sudah ada: ' + duplicates.join(', '));
                return false;
            }
        });

        document.getElementById('add-more').addEventListener('click', function () {
            const container = document.getElementById('bahanbaku-container');
            const original = container.querySelector('.bahanbaku-group');
            const clone = original.cloneNode(true);

            // Kosongkan semua input dalam clone
            clone.querySelectorAll('input').forEach(input => input.value = '');
            clone.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
            
            // Remove any existing error states
            clone.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            clone.querySelectorAll('.kode-error').forEach(el => el.textContent = '');

            container.appendChild(clone);
        });

        // Show server-side validation errors if any
        @if(session('alert'))
            alert('{{ session('alert') }}');
        @endif
    </script>
@endsection
