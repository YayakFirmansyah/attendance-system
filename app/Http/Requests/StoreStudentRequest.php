<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'student_id' => ['required', 'string', 'max:50', 'unique:students,student_id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'cohort_id' => ['required', 'exists:cohorts,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive,graduated'],
            'profile_photo' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,jpg,png'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'NIM/Student ID',
            'cohort_id' => 'cohort',
            'profile_photo' => 'foto profil',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'NIM wajib diisi.',
            'student_id.unique' => 'NIM sudah terdaftar.',
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'cohort_id.required' => 'Cohort wajib dipilih.',
            'profile_photo.image' => 'File harus berupa gambar.',
            'profile_photo.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }
}
