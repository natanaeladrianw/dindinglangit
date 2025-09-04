<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBahanBakuNotaBeliRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nota_beli_id' => 'required|exists:nota_belis,id',
            'bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'satuan_id' => 'required|exists:satuans,id',
            'tanggal_transaksi' => 'required|date',
            'harga' => 'required',
            'jumlah' => 'required',
            'tgl_exp' => 'nullable|date|after_or_equal:today',
        ];
    }
}
