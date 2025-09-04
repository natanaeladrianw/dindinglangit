@extends('layouts.master')

@section('content')
<style>
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .text-warning {
        color: #856404 !important;
    }
    
    .text-success {
        color: #155724 !important;
    }
    
    .text-info {
        color: #0c5460 !important;
    }
    
    .quantity-limit {
        border-color: #dc3545 !important;
        background-color: #fff5f5 !important;
    }
    
    .quantity-limit:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    
    .input-error {
        border-color: #dc3545 !important;
        background-color: #fff5f5 !important;
    }
    
    .input-error:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    
    .card.disabled {
        cursor: not-allowed !important;
    }
    
    .status-message {
        font-size: 0.8rem;
        line-height: 1.2;
    }
</style>
    <form action="{{ route('transaksi.store') }}" method="post">
        @csrf
        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
        {{-- @dd($shiftKasir->id) --}}
        <input type="hidden" name="shift_kasir_id" value="{{ $shiftKasir->id }}">
        <input type="hidden" name="tanggal" value="{{ now() }}">

        <div class="row">
            <!-- Kolom Menu -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-dark">
                        <h5 class="text-light">Daftar Menu</h5>
                        <input type="text" id="search-menu" class="form-control mt-2" placeholder="Cari menu...">
                    </div>
                    <div class="card-body">
    <div class="row" id="menu-list">
        @foreach($menus->groupBy('kategoriMenus.jenis_kategori_menu') as $kategori => $items)
            <div class="col-12 mt-3 kategori-wrapper">
                <h6 class="text-muted">{{ $kategori }}</h6>
                <div class="row">
                    @foreach($items as $menu)
                    <div class="col-md-3 mb-3 menuku"
                         data-id="{{ $menu->id }}"
                         data-name="{{ $menu->nama_item }}"
                         data-price="{{ $menu->harga }}"
                         data-max-pesanan="{{ $menu->max_pesanan ?? 'unlimited' }}"
                         @if($menu->status !== 'available') data-disabled="true" @endif>

                        <div class="card h-100 @if($menu->status === 'available') cursor-pointer @else disabled @endif"
                             @if($menu->status === 'available') onclick="addToOrder(this)" @endif
                             style="@if($menu->status !== 'available') opacity: 0.6; background-color: #f8f9fa; @endif">

                            <div class="card-body">
                                <h6 class="text-dark">{{ $menu->nama_item }}</h6>
                                <p class="text-dark">Rp {{ number_format($menu->harga, 0, ',', '.') }}</p>
                                
                                @if($menu->status === 'available' && isset($menu->info_stok))
                                    <div class="mt-2">
                                        <span class="badge bg-success">Tersedia</span>
                                        <small class="d-block text-success mt-1 status-message">{{ $menu->info_stok }}</small>
                                    </div>
                                @endif

                                @if($menu->status === 'no_recipe')
                                    <div class="mt-2">
                                        <span class="badge bg-info">Belum Ada Resep</span>
                                        <small class="d-block text-info mt-1 status-message">{{ $menu->alasan_tidak_tersedia }}</small>
                                    </div>
                                @elseif($menu->status === 'all_stock_empty')
                                    <div class="mt-2">
                                        <span class="badge bg-danger">Stok Habis</span>
                                        <small class="d-block text-danger mt-1 status-message">{{ $menu->alasan_tidak_tersedia }}</small>
                                    </div>
                                @elseif($menu->status === 'partial_stock_empty')
                                    <div class="mt-2">
                                        <span class="badge bg-warning">Stok Terbatas</span>
                                        <small class="d-block text-warning mt-1 status-message">{{ $menu->alasan_tidak_tersedia }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
                </div>
            </div>

            <!-- Kolom Order -->
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark">
                        <h5 class="text-light">Order Detail</h5>
                    </div>
                    <div class="card-body">
                        <div id="order-items">
                            <p class="text-muted text-center py-3">Belum ada item</p>
                        </div>

                        <div class="order-summary border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span class="fw-bold" id="subtotal">Rp 0</span>
                            </div>
                            <input type="hidden" name="total_pembayaran" id="total_pembayaran">

                            <div class="mb-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                                    <option value="Cash">Tunai (Cash)</option>
                                    <option value="QRIS">QRIS</option>
                                </select>
                            </div>
                            <div class="mb-3" id="tunai_fields" style="display: block;">
                                <label class="form-label">Uang Konsumen</label>
                                <input type="text" inputMode="numeric" id="uang_konsumen" class="form-control" min="0">
                            </div>
                            <div class="mb-3" id="kembalian_fields" style="display: block;">
                                <label class="form-label">Uang Kembalian</label>
                                <input type="text" id="uang_kembalian" class="form-control" readonly>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 py-2">
                                Bayar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Template untuk item order (hidden) -->
    <template id="order-item-template">
        <div class="order-item py-2 border-bottom" data-id="">
            <div class="d-flex justify-content-between">
                <div class="d-flex flex-column">
                    <h6 class="item-name mb-1 text-dark"></h6>
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <button type="button" class="btn btn-outline-secondary decrement">-</button>
                        <input type="number" class="form-control text-center quantity" value="1" min="1" name="jumlah[]">
                        <button type="button" class="btn btn-outline-secondary increment">+</button>
                    </div>
                </div>
                <div class="text-end">
                    <span class="item-price text-dark"></span>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-item">
                        <i class="fas fa-times"></i>
                    </button>
                    <input type="hidden" class="item-id" name="menu_id[]">
                    <input type="hidden" class="item-price-value" name="harga[]">
                </div>
            </div>
            <div class="mt-2">
                <input type="text" class="form-control form-control-sm notes" placeholder="Catatan" name="catatan[]">
            </div>
        </div>
    </template>

    <script>
        // Fungsi untuk menambahkan item ke order
        function addToOrder(element) {
            const menuItem = $(element).closest('.menuku');
            const id = menuItem.data('id');
            const name = menuItem.data('name');
            const price = menuItem.data('price');
            const maxPesanan = menuItem.data('max-pesanan');

            // Cek apakah item sudah ada di order
            let existingItem = $(`#order-items .order-item[data-id="${id}"]`);

            if (existingItem.length > 0) {
                // Jika sudah ada, tambah jumlah
                const quantityInput = existingItem.find('.quantity');
                const currentQuantity = parseInt(quantityInput.val());
                
                // Validasi maksimal pesanan
                if (maxPesanan !== 'unlimited' && currentQuantity >= maxPesanan) {
                    alert(`Menu ${name} hanya dapat dipesan maksimal ${maxPesanan} kali`);
                    return;
                }
                
                quantityInput.val(currentQuantity + 1);
            } else {
                // Jika belum ada, buat item baru
                const template = $('#order-item-template').html();
                const $newItem = $(template);
                $newItem.attr('data-id', id);
                $newItem.attr('data-max-pesanan', maxPesanan);
                $newItem.find('.item-name').text(name);
                $newItem.find('.item-price').text(formatRupiah(price));
                $newItem.find('.item-id').val(id);
                $newItem.find('.item-price-value').val(price);

                if ($('#order-items p.text-muted').length > 0) {
                    $('#order-items').html($newItem);
                } else {
                    $('#order-items').append($newItem);
                }
            }

            updateTotal();
        }

        // Fungsi untuk update total
        function updateTotal() {
            let subtotal = 0;

            $('.order-item').each(function() {
                const price = parseFloat($(this).find('.item-price-value').val());
                const quantity = parseInt($(this).find('.quantity').val());
                subtotal += price * quantity;
            });

            $('#subtotal').text(formatRupiah(subtotal));
            $('#total_pembayaran').val(subtotal);
        }

        // Format mata uang Rupiah
        function formatRupiah(angka) {
            return 'Rp ' + angka.toLocaleString('id-ID');
        }

        // Event delegation untuk interaksi order
        $(document).on('click', '.increment', function() {
            const orderItem = $(this).closest('.order-item');
            const input = $(this).siblings('.quantity');
            const currentQuantity = parseInt(input.val());
            const maxPesanan = orderItem.data('max-pesanan');
            const itemName = orderItem.find('.item-name').text();
            
            // Validasi maksimal pesanan
            if (maxPesanan !== 'unlimited' && currentQuantity >= maxPesanan) {
                alert(`Menu ${itemName} hanya dapat dipesan maksimal ${maxPesanan} kali`);
                return;
            }
            
            input.val(currentQuantity + 1);
            
            // Visual feedback untuk quantity yang mencapai batas maksimal
            if (maxPesanan !== 'unlimited' && (currentQuantity + 1) >= maxPesanan) {
                input.addClass('quantity-limit');
            } else {
                input.removeClass('quantity-limit');
            }
            
            updateTotal();
        });

        $(document).on('click', '.decrement', function() {
            const input = $(this).siblings('.quantity');
            if (parseInt(input.val()) > 1) {
                input.val(parseInt(input.val()) - 1);
                updateTotal();
            }
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('.order-item').remove();
            if ($('#order-items .order-item').length === 0) {
                $('#order-items').html('<p class="text-muted text-center py-3">Belum ada item</p>');
            }
            updateTotal();
        });

        $(document).on('change', '.quantity', function() {
            const orderItem = $(this).closest('.order-item');
            const input = $(this);
            const newQuantity = parseInt(input.val());
            const maxPesanan = orderItem.data('max-pesanan');
            const itemName = orderItem.find('.item-name').text();
            
            // Validasi maksimal pesanan
            if (maxPesanan !== 'unlimited' && newQuantity > maxPesanan) {
                alert(`Menu ${itemName} hanya dapat dipesan maksimal ${maxPesanan} kali`);
                input.val(maxPesanan);
            }
            
            // Validasi minimal quantity
            if (newQuantity < 1) {
                input.val(1);
            }
            
            // Visual feedback untuk quantity yang mencapai batas maksimal
            if (maxPesanan !== 'unlimited' && newQuantity >= maxPesanan) {
                input.addClass('quantity-limit');
            } else {
                input.removeClass('quantity-limit');
            }
            
            updateTotal();
        });

        // Fungsi pencarian menu
        $('#search-menu').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            console.log('Search Term:', searchTerm);

            // 1. Tampilkan semua kategori dan semua menu di awal
            $('.kategori-wrapper').show();
            $('.menuku').show();

            // 2. Filter berdasarkan pencarian
            $('.menuku').each(function() {
                const menuName = ($(this).data('name') || '').toLowerCase();

                if (!menuName.includes(searchTerm)) {
                    $(this).hide();
                }
            });

            // 3. Hide kategori-wrapper kalau semua menuku di dalamnya ke-hide
            $('.kategori-wrapper').each(function() {
                const visibleItems = $(this).find('.menuku:visible').length;

                if (visibleItems === 0) {
                    $(this).hide();
                }
            });
        });
    </script>

    <script>
                $(document).ready(function() {
            // Sembunyikan field tunai jika metode bukan cash
            toggleCashFields();
            
            // Hitung kembalian jika metode default adalah Tunai
            if ($('#metode_pembayaran').val() === 'Cash') {
                calculateChange();
            }
            
            $('#metode_pembayaran').change(function() {
                toggleCashFields();
                resetCashFields();
                // Hapus class error saat user memilih metode pembayaran
                $(this).removeClass('select-error');
                
                // Jika memilih Tunai, hitung kembalian
                if ($(this).val() === 'Cash') {
                    calculateChange();
                }
            });

            $('#uang_konsumen').on('input', function() {
                calculateChange();
                // Hapus class error saat user mulai mengetik
                $(this).removeClass('input-error');
            });

            // Validasi form sebelum submit
            $('form').on('submit', function(e) {
                // Cek apakah ada item yang dipesan
                const orderItems = $('#order-items .order-item');
                if (orderItems.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih menu terlebih dahulu!');
                    return false;
                }
                
                // Cek apakah total pembayaran valid
                const total = parseFloat($('#total_pembayaran').val()) || 0;
                if (total <= 0) {
                    e.preventDefault();
                    alert('Total pembayaran tidak valid!');
                    return false;
                }
                
                const metodePembayaran = $('#metode_pembayaran').val();
                
                // Validasi metode pembayaran
                if (!metodePembayaran) {
                    e.preventDefault();
                    $('#metode_pembayaran').addClass('select-error');
                    alert('⚠️ PERHATIAN!\n\nSilakan pilih metode pembayaran terlebih dahulu!');
                    $('#metode_pembayaran').focus();
                    return false;
                } else {
                    $('#metode_pembayaran').removeClass('select-error');
                }
                
                const uangKonsumen = $('#uang_konsumen').val().replace(/\D/g, '');
                
                // Hapus class error sebelumnya
                $('#uang_konsumen').removeClass('input-error');
                
                if (metodePembayaran === 'Cash') {
                    if (!uangKonsumen || uangKonsumen === '0') {
                        e.preventDefault();
                        $('#uang_konsumen').addClass('input-error');
                        alert('⚠️ PERHATIAN!\n\nSilakan input dulu jumlah uang konsumen untuk metode pembayaran Tunai.\n\nTotal yang harus dibayar: Rp ' + total.toLocaleString('id-ID'));
                        $('#uang_konsumen').focus();
                        return false;
                    }
                    
                    if (parseFloat(uangKonsumen) < total) {
                        e.preventDefault();
                        $('#uang_konsumen').addClass('input-error');
                        alert('⚠️ PERHATIAN!\n\nUang konsumen tidak mencukupi untuk membayar total belanja.\n\nTotal: Rp ' + total.toLocaleString('id-ID') + '\nUang: Rp ' + parseFloat(uangKonsumen).toLocaleString('id-ID') + '\nKurang: Rp ' + (total - parseFloat(uangKonsumen)).toLocaleString('id-ID'));
                        $('#uang_konsumen').focus();
                        return false;
                    }
                } else if (metodePembayaran === 'QRIS') {
                    // Untuk QRIS, tidak perlu validasi uang konsumen
                    // Field uang konsumen akan otomatis tersembunyi
                }
            });

            function toggleCashFields() {
                const selectedValue = $('#metode_pembayaran').val();
                
                if (selectedValue === 'Cash') {
                    $('#tunai_fields, #kembalian_fields').show();
                } else {
                    $('#tunai_fields, #kembalian_fields').hide();
                }
            }

            // Format mata uang Rupiah untuk kembalian
            function formatRupiahKembalian(angka) {
                if (typeof angka !== 'number') angka = parseFloat(angka);
                return 'Rp ' + angka.toLocaleString('id-ID');
            }

            function calculateChange() {
                const total = parseFloat($('#total_pembayaran').val()) || 0;
                const uangDiberikan = parseFloat($('#uang_konsumen').val().replace(/\D/g, '')) || 0;

                if (uangDiberikan >= total && uangDiberikan > 0) {
                    const kembalian = uangDiberikan - total;
                    const kembalianRp = formatRupiahKembalian(kembalian);

                    $('#uang_kembalian').val(kembalianRp);
                } else {
                    const kembalianRp = formatRupiahKembalian(0);
                    $('#uang_kembalian').val(kembalianRp);
                }
            }

            function resetCashFields() {
                $('#uang_konsumen').val('');
                $('#uang_kembalian').val('');
                // Hapus class error saat reset
                $('#uang_konsumen').removeClass('input-error');
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#uang_konsumen').on('input', function () {
                let input = $(this);
                let value = input.val().replace(/\D/g, ''); // hanya angka

                // Format ribuan pakai titik
                let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                input.val(formatted); // tampilkan ke input

                // Hitung kembalian setelah input berubah
                calculateChange();
            });
        });
    </script>

    <script>
        var msg = '{{ Session::get('alert') }}';

        var exist = '{{ Session::has('alert') }}';

        if (exist) {
            alert(msg);
        }
    </script>
@endsection
