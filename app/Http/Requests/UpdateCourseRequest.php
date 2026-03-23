<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
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
        $courseId = $this->route('course');

        return [
            'course_code' => ['required', 'string', 'max:20', Rule::unique('courses', 'course_code')->ignore($courseId)],
            'course_name' => ['required', 'string', 'max:255'],
            'credits' => ['required', 'integer', 'min:1', 'max:6'],
            'faculty' => ['required', 'string', 'max:255'],
            'lecturer_id' => ['required', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'course_code' => 'kode mata kuliah',
            'course_name' => 'nama mata kuliah',
            'credits' => 'SKS',
            'faculty' => 'fakultas',
            'lecturer_id' => 'dosen pengampu',
            'description' => 'deskripsi',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'course_code.required' => 'Kode mata kuliah wajib diisi.',
            'course_code.unique' => 'Kode mata kuliah sudah terdaftar.',
            'course_name.required' => 'Nama mata kuliah wajib diisi.',
            'credits.required' => 'SKS wajib diisi.',
            'credits.min' => 'SKS minimal 1.',
            'credits.max' => 'SKS maksimal 6.',
            'lecturer_id.required' => 'Dosen pengampu wajib dipilih.',
            'lecturer_id.exists' => 'Dosen tidak ditemukan.',
        ];
    }
}
