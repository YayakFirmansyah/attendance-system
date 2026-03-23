<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $faceThreshold = (float) Setting::getValue('face_similarity_threshold', config('app.face_similarity_threshold', 0.5));

        return view('settings.index', compact('faceThreshold'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'face_similarity_threshold' => ['required', 'numeric', 'min:0.1', 'max:0.99'],
        ]);

        Setting::setValue('face_similarity_threshold', $validated['face_similarity_threshold'], 'float');

        return redirect()->route('settings.index')->with('success', 'Pengaturan confidence berhasil diperbarui.');
    }
}
