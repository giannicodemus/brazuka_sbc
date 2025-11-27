<?php

namespace App\Http\Requests\OutboundRoutes;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'voip_account_id' => ['required', 'exists:voip_accounts,id'],
            'voip_trunk_id' => ['required', 'exists:voip_trunks,id'],
            'random_clid_list_id' => ['nullable', 'exists:random_clid_lists,id'],
            'recording' => ['nullable', 'integer'],

        ];
    }

    public function messages(): array
    {
        return [
            'voip_account_id.required' => 'A Conta VoIP é obrigatória.',
            'voip_account_id.exists' => 'A Conta VoIP selecionada é inválida.',
            'voip_trunk_id.required' => 'O Tronco VoIP é obrigatório.',
            'voip_trunk_id.exists' => 'O Tronco VoIP selecionado é inválido.',
            'random_clid_list_id.exists' => 'O Caller ID selecionado é inválido.',
        ];
    }
}
