<?php

namespace App\Http\Requests\Backup;

use Illuminate\Foundation\Http\FormRequest;

class TarikApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true; // Adjust if you have authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'departemen_id' => 'required|exists:departemen,id',
            'api_url'       => [
                'required',
                'url',
                function ($attribute, $value, $fail) {
                    $parsed = parse_url($value);
                    if (!isset($parsed['scheme']) || !in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
                        return $fail('URL harus menggunakan protokol HTTP atau HTTPS yang valid.');
                    }
                    $host = $parsed['host'] ?? '';
                    if (empty($host)) {
                        return $fail('Host URL tidak valid.');
                    }
                    if (!app()->environment('local')) {
                        $ip = gethostbyname($host);
                        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                            return $fail('Alamat URL tidak boleh mengarah ke IP privat atau loopback internal (SSRF Protection).');
                        }
                    }
                },
            ],
            'api_token'     => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'departemen_id.required' => 'Pilih Departemen Tujuan terlebih dahulu.',
            'departemen_id.exists'   => 'Departemen yang dipilih tidak valid.',
            'api_url.required'       => 'Masukkan URL Web Lama yang sedang online di Railway.',
            'api_url.url'            => 'Format URL tidak valid (harus diawali http:// atau https://).',
            'api_token.required'     => 'Masukkan Token Rahasia API.',
        ];
    }
}
?>
