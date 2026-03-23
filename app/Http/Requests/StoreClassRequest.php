<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'class_code' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'string', 'max:50'],
            'day' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:200'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'course_id' => 'mata kuliah',
            'room_id' => 'ruangan',
            'class_code' => 'kode kelas',
            'semester' => 'semester',
            'day' => 'hari',
            'start_time' => 'waktu mulai',
            'end_time' => 'waktu selesai',
            'capacity' => 'kapasitas',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'course_id.required' => 'Mata kuliah wajib dipilih.',
            'course_id.exists' => 'Mata kuliah tidak ditemukan.',
            'room_id.required' => 'Ruangan wajib dipilih.',
            'room_id.exists' => 'Ruangan tidak ditemukan.',
            'class_code.required' => 'Kode kelas wajib diisi.',
            'day.required' => 'Hari wajib dipilih.',
            'day.in' => 'Hari tidak valid.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date_format' => 'Format waktu tidak valid (HH:MM).',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'capacity.min' => 'Kapasitas minimal 1.',
            'capacity.max' => 'Kapasitas maksimal 200.',
        ];
    }
}
