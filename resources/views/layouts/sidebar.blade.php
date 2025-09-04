<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo mb-3">
        <a href="/dashboard" class="app-brand-link">
            <img class="img-fluid" style="height: 50px" src="{{ asset('assets/img/logo.jpeg') }}" alt="">
            <span class="demo menu-text fw-bolder ms-4 text-dark fs-3">Dinding Langit</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Selamat datang!</span>
        </li>
        <li class="menu-item">
            <a href="{{ auth()->user()->role === 'owner' ? '/laporan-pembelian-bahan-baku' : '/dashboard' }}" class="menu-link">
                <i class="fa-solid me-3 fa-user"></i>
                <div class="d-flex flex-column">
                    <strong data-i18n="Analytics">{{ Auth::user()->name }}</strong>
                    <small data-i18n="Analytics">{{ Auth::user()->role }}</small>
                </div>
            </a>
        </li>

        <!-- Dashboard -->
        @if(auth()->user()->role !== 'owner')
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Dashboard</span>
        </li>
        <li class="menu-item {{ ($title === 'Dashboard') ? 'active' : '' }}">
            <a href="/dashboard" class="menu-link">
                <i class="fa-solid me-3 fa-house"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>
        @endif

        @if(auth()->user()->role === 'owner')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Laporan</span>
            </li>
            <li class="menu-item {{ ($title === 'Laporan Pembelian Bahan Baku') ? 'active' : '' }}">
                <a href="/laporan-pembelian-bahan-baku" class="menu-link">
                    <i class="fa-solid fa-clipboard me-3"></i>
                    <div data-i18n="Basic">Pembelian Bahan Baku</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Laporan Pengiriman Bahan Baku') ? 'active' : '' }}">
                <a href="/laporan-pengiriman-bahan-baku" class="menu-link">
                    <i class="fa-solid fa-truck me-3"></i>
                    <div data-i18n="Basic">Pengiriman Bahan Baku</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Laporan Penggunaan Bahan Baku') ? 'active' : '' }}">
                <a href="/laporan-penggunaan-bahan-baku" class="menu-link">
                    <i class="fa-solid fa-clipboard me-3"></i>
                    <div data-i18n="Basic">Penggunaan Bahan Baku</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Laporan Penjualan') ? 'active' : '' }}">
                <a href="/laporan-penjualan" class="menu-link">
                    <i class="fa-solid fa-clipboard me-3"></i>
                    <div data-i18n="Basic">Penjualan Menu</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Manajemen User</span>
            </li>
            <li class="menu-item {{ ($title === 'User') ? 'active' : '' }}">
                <a href="/user" class="menu-link">
                    <i class="fa-solid me-3 fa-users"></i>
                    <div data-i18n="Basic">User</div>
                </a>
            </li>

        @elseif(auth()->user()->role === 'admin_gudang')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Data</span>
            </li>
            <li class="menu-item {{ ($title === 'Satuan') ? 'active' : '' }}">
                <a href="{{ route('satuan.index') }}" class="menu-link">
                    <i class="fa-solid me-3 fa-box-open"></i>
                    <div data-i18n="Basic">Satuan</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Bahan Baku') ? 'active' : '' }}">
                <a href="{{ route('bahanbaku.index') }}" class="menu-link">
                    <i class="fa-solid me-3 fa-box-open"></i>
                    <div data-i18n="Basic">Bahan Baku</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Grup Bahan Baku') ? 'active' : '' }}">
                <a href="/grupbahanbaku" class="menu-link">
                    <i class="fa-solid me-3 fa-box-open"></i>
                    <div data-i18n="Basic">Grup Bahan Baku</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Kategori Bahan Baku') ? 'active' : '' }}">
                <a href="/kategoribahanbaku" class="menu-link">
                    <i class="fa-solid fa-boxes-stacked me-3"></i>
                    <div data-i18n="Basic">Kategori Bahan Baku</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Supplier') ? 'active' : '' }}">
                <a href="/supplier" class="menu-link">
                    <i class="fa-solid me-3 fa-truck"></i>
                    <div data-i18n="Basic">Supplier</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Stok</span>
            </li>
            <li class="menu-item {{ ($title === 'Stok Gudang') ? 'active' : '' }}">
                <a href="/stokgudang" class="menu-link">
                    <i class="fa-solid me-3 fa-warehouse"></i>
                    <div data-i18n="Basic">Stok Gudang</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Stok Dapur') ? 'active' : '' }}">
                <a href="/stokdapur" class="menu-link">
                    <i class="fa-solid me-3 fa-warehouse"></i>
                    <div data-i18n="Basic">Stok Dapur</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Prediksi Stok') ? 'active' : '' }}">
                <a href="/wma-prediksi" class="menu-link">
                    <i class="me-3 fa-solid fa-chart-simple"></i>
                    <div data-i18n="Basic">Prediksi Stok (WMA)</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Stok Opname') ? 'active' : '' }}">
                <a href="/stokopname" class="menu-link">
                    <i class="fa-solid fa-clipboard me-3"></i>
                    <div data-i18n="Basic">Stok Opname</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Satuan Minimal') ? 'active' : '' }}">
                <a href="{{ route('stok-minimal.index') }}" class="menu-link">
                    <i class="fa-solid me-3 fa-triangle-exclamation"></i>
                    <div data-i18n="Basic">Stok Minimal</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Informasi</span>
            </li>
            <li class="menu-item {{ ($title === 'Laporan Pengiriman Bahan Baku') ? 'active' : '' }}">
                <a href="/laporan-pengiriman-bahan-baku" class="menu-link">
                    <i class="fa-solid fa-truck me-3"></i>
                    <div data-i18n="Basic">Pengiriman Bahan Baku</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Laporan Pembelian Bahan Baku') ? 'active' : '' }}">
                <a href="/laporan-pembelian-bahan-baku" class="menu-link">
                    <i class="fa-solid fa-clipboard me-3"></i>
                    <div data-i18n="Basic">Pembelian Bahan Baku</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Nota Beli') ? 'active' : '' }}">
                <a href="/notabeli" class="menu-link">
                    <i class="fa-solid fa-receipt me-3"></i>
                    <div data-i18n="Basic">Nota Beli</div>
                </a>
            </li>
        @elseif(auth()->user()->role === 'admin_dapur')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Data</span>
            </li>

            <li class="menu-item {{ ($title === 'Resep') ? 'active' : '' }}">
                <a href="/resep" class="menu-link">
                    <i class="fa-solid me-3 fa-utensils"></i>
                    <div data-i18n="Basic">Resep</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Menu') ? 'active' : '' }}">
                <a href="/menu" class="menu-link">
                    <i class="fa-solid me-3 fa-utensils"></i>
                    <div data-i18n="Basic">Menu</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Kategori Menu') ? 'active' : '' }}">
                <a href="/kategorimenu" class="menu-link">
                    <i class="fa-solid me-3 fa-list"></i>
                    <div data-i18n="Basic">Kategori Menu</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Penggunaan Bahan') ? 'active' : '' }}">
                <a href="/penggunaanbahanbaku" class="menu-link">
                    <i class="fa-solid me-3 fa-box-open"></i>
                    <div data-i18n="Basic">Penggunaan Bahan</div>
                </a>
            </li>
            {{-- <li class="menu-item {{ ($title === 'Bahan Baku') ? 'active' : '' }}">
                <a href="{{ route('bahanbaku.index') }}" class="menu-link">
                    <i class="fa-solid me-3 fa-box-open"></i>
                    <div data-i18n="Basic">Bahan Baku</div>
                </a>
            </li> --}}

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Stok</span>
            </li>
            <li class="menu-item {{ ($title === 'Stok Opname') ? 'active' : '' }}">
                <a href="/stokopname" class="menu-link">
                    <i class="fa-solid fa-clipboard me-3"></i>
                    <div data-i18n="Basic">Stok Opname</div>
                </a>
            </li>
            <li class="menu-item {{ ($title === 'Stok Dapur') ? 'active' : '' }}">
                <a href="/stokdapur" class="menu-link">
                    <i class="fa-solid me-3 fa-warehouse"></i>
                    <div data-i18n="Basic">Stok Dapur</div>
                </a>
            </li>

        @elseif(auth()->user()->role === 'kasir')
            @php
                $saldoAwalKosong = App\Models\ShiftKasir::where('user_id', Auth::user()->id)
                        ->whereNull('jam_keluar')
                        ->whereNull('saldo_awal')
                        ->latest()
                        ->first();
                $saldoAkhirKosong = App\Models\ShiftKasir::where('user_id', Auth::user()->id)
                        ->whereNull('jam_keluar')
                        ->whereNull('saldo_akhir')
                        ->latest()
                        ->first();
            @endphp
            {{-- <li class="menu-item {{ ($title === 'Shift') ? 'active' : '' }}">
                @if ($saldoAwalKosong && $saldoAwalKosong->saldo_awal === null)
                    <a data-bs-toggle="modal" data-bs-target="#saldoAwalModal" class="menu-link cursor-pointer">
                        <i class="fa-solid me-3 fa-clock"></i>
                        <div data-i18n="Basic">Shift</div>
                    </a>
                @else
                    <a href="/shiftkasir" class="menu-link">
                        <i class="fa-solid me-3 fa-clock"></i>
                        <div data-i18n="Basic">Shift</div>
                    </a>
                @endif
            </li> --}}
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Transaksi</span>
            </li>
            <li class="menu-item {{ ($title === 'Order') ? 'active' : '' }}">
                @if ($saldoAwalKosong && $saldoAwalKosong->saldo_awal === null)
                    <a data-bs-toggle="modal" data-bs-target="#saldoAwalModal" class="menu-link cursor-pointer">
                        <i class="fa-solid fa-dollar me-3"></i>
                        <div data-i18n="Basic">Order</div>
                    </a>
                @else
                    <a href="{{ route('transaksi.create') }}" class="menu-link">
                        <i class="fa-solid fa-dollar me-3"></i>
                        <div data-i18n="Basic">Order</div>
                    </a>
                @endif
            </li>
            <li class="menu-item {{ ($title === 'Transaksi') ? 'active' : '' }}">
                @if ($saldoAwalKosong && $saldoAwalKosong->saldo_awal === null)
                    <a data-bs-toggle="modal" data-bs-target="#saldoAwalModal" class="menu-link cursor-pointer">
                        <i class="fa-solid fa-cash-register me-3"></i>
                        <div data-i18n="Basic">Transaksi</div>
                    </a>
                @else
                    <a href="/transaksi" class="menu-link">
                        <i class="fa-solid fa-cash-register me-3"></i>
                        <div data-i18n="Basic">Transaksi</div>
                    </a>
                @endif
            </li>
        @endif

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">LOGOUT</span>
        </li>
        <li class="menu-item">
            @if (auth()->user()->role === 'kasir' && $saldoAwalKosong && $saldoAwalKosong->saldo_awal === null)
                <a data-bs-toggle="modal" data-bs-target="#saldoAwalModal" class="menu-link cursor-pointer">
                    <i class="fa-solid me-3 fa-right-from-bracket text-danger"></i>
                    <div data-i18n="Basic" class="text-danger">Keluar</div>
                </a>
            @elseif (auth()->user()->role === 'kasir' && $saldoAkhirKosong && $saldoAkhirKosong->saldo_akhir === null)
                <a data-bs-toggle="modal" data-bs-target="#saldoAkhirModal" class="menu-link cursor-pointer">
                    <i class="fa-solid me-3 fa-right-from-bracket text-danger"></i>
                    <div data-i18n="Basic" class="text-danger">Keluar</div>
                </a>
            @else
                <a data-bs-target="#logoutModal" data-bs-toggle="modal" class="menu-link cursor-pointer">
                    <i class="fa-solid me-3 fa-right-from-bracket text-danger"></i>
                    <div data-i18n="Basic" class="text-danger">Keluar</div>
                </a>
            @endif
        </li>
    </ul>
</aside>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h5 class="text-black">Apakah anda yakin untuk keluar?</h5>
                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <form action="/signout" method="post">
                        @csrf
                        <button class="ms-2 btn btn-danger">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Saldo Awal -->
<div class="modal fade" id="saldoAwalModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h5 class="text-black">Input Saldo Awal</h5>
                <form action="/saldo-awal-kasir" method="post">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
                    <input type="hidden" name="saldo_awal" id="saldo_awal_sidebar">
                    <input type="text" inputmode="numeric" class="form-control saldo-awal-sidebar">
                    <button class="mt-3 btn btn-dark" type="submit">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Saldo Akhir -->
<div class="modal fade" id="saldoAkhirModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h5 class="text-black">Input Saldo Akhir</h5>
                <form action="/saldo-akhir-kasir" method="post">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
                    <input type="hidden" class="form-control" name="saldo_akhir" id="saldo_akhir_sidebar">
                    <input type="text" inputmode="numeric" class="form-control saldo-akhir-sidebar">
                    <button class="mt-3 btn btn-dark" type="submit">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.saldo-awal-sidebar').on('input', function () {
            let input = $(this);
            let value = input.val().replace(/\D/g, ''); // hanya angka

            // Format ribuan pakai titik
            let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

            input.val(formatted); // tampilkan ke input

            // Update hidden input (tanpa format)
            $('#saldo_awal_sidebar').val(value);
        });

        $('.saldo-akhir-sidebar').on('input', function () {
            let input = $(this);
            let value = input.val().replace(/\D/g, ''); // hanya angka

            // Format ribuan pakai titik
            let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

            input.val(formatted); // tampilkan ke input

            // Update hidden input (tanpa format)
            $('#saldo_akhir_sidebar').val(value);
        });
    });
</script>
