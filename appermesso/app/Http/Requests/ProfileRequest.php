<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
            'cognome' => ['required', 'string', 'max:100'],
            'matricola' => ['nullable', 'string', 'max:50'],
            'centro_costo' => ['nullable', 'string', 'max:100'],
            'livello' => ['nullable', Rule::in(['D2', 'C2', 'C3', 'B1'])],
            'qualifica' => ['nullable', Rule::in(['Operaio', 'Impiegato'])],
            'ente' => ['nullable', 'string', 'max:150'],
        ];
    }
}
