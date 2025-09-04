<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBahanBakuRequest extends FormRequest
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
            'kategori_bahan_baku_id' => 'required|exists:kategori_bahan_bakus,id',
            'grup_bahan_baku_id' => 'required|exists:grup_bahan_bakus,id',
            'kode' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
        ];
    }
}
