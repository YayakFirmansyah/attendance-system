<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use Illuminate\Http\Request;

class CohortController extends Controller
{
    public function index(Request $request)
    {
        $query = Cohort::query();
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('angkatan', 'like', "%{$search}%")
                  ->orWhere('program_studi', 'like', "%{$search}%")
                  ->orWhere('kelas', 'like', "%{$search}%");
        }
        $cohorts = $query->latest()->get();
        return view('cohorts.index', compact('cohorts'));
    }

    public function create()
    {
        return view('cohorts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'angkatan' => 'required|integer',
            'fakultas' => 'required|string|max:255',
            'program_studi' => 'required|string|max:255',
            'kelas' => 'required|string|max:50',
            'semester' => 'required|integer|min:1|max:14',
        ]);
        
        Cohort::create($validated);
        
        return redirect()->route('cohorts.index')->with('success', 'Cohort created successfully.');
    }

    public function show(Cohort $cohort)
    {
        return view('cohorts.show', compact('cohort'));
    }

    public function edit(Cohort $cohort)
    {
        return view('cohorts.edit', compact('cohort'));
    }

    public function update(Request $request, Cohort $cohort)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'angkatan' => 'required|integer',
            'fakultas' => 'required|string|max:255',
            'program_studi' => 'required|string|max:255',
            'kelas' => 'required|string|max:50',
            'semester' => 'required|integer|min:1|max:14',
        ]);
        
        $cohort->update($validated);
        
        return redirect()->route('cohorts.index')->with('success', 'Cohort updated successfully.');
    }

    public function destroy(Cohort $cohort)
    {
        try {
            $cohort->delete();
            return redirect()->route('cohorts.index')->with('success', 'Cohort deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('cohorts.index')->with('error', 'Cannot delete cohort as it might be used by students or classes.');
        }
    }
}
