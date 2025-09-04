<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBahanBakuNotaBeliRequest extends FormRequest
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
            'bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'harga' => 'required',
            'jumlah' => 'required',
            'satuan_id' => 'required|exists:satuans,id',
            'tanggal_transaksi' => 'required|date',
            'tgl_exp' => 'nullable|date|after_or_equal:today',
        ];
    }
}
