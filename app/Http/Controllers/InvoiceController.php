<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function cetakInvoice($id)
    {
        $transaksi = Transaksi::with(['menus', 'users'])->findOrFail($id);

        $pdf = Pdf::loadView('pages.invoice', compact('transaksi'));
        return $pdf->download('Invoice_'.$transaksi->id.'.pdf');
    }
}
