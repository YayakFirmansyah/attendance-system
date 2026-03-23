<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isDosen();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'check_in' => ['nullable', 'date_format:H:i:s'],
            'check_out' => ['nullable', 'date_format:H:i:s', 'after:check_in'],
            'status' => ['required', 'in:present,late,absent,excused'],
            'notes' => ['nullable', 'string', 'max:500'],
            'excused_reason' => ['required_if:status,excused', 'nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'check_in' => 'waktu masuk',
            'check_out' => 'waktu keluar',
            'status' => 'status kehadiran',
            'notes' => 'catatan',
            'excused_reason' => 'alasan izin',
            'attachment' => 'lampiran',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'check_out.after' => 'Waktu keluar harus setelah waktu masuk.',
            'status.required' => 'Status kehadiran wajib dipilih.',
            'status.in' => 'Status kehadiran tidak valid.',
            'excused_reason.required_if' => 'Alasan izin wajib diisi untuk status izin.',
            'attachment.max' => 'Ukuran file maksimal 5MB.',
            'attachment.mimes' => 'Format file harus PDF, JPG, JPEG, atau PNG.',
        ];
    }
}
