@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">System Settings</h2>
                <p class="text-muted mb-0">Atur parameter confidence untuk validasi face recognition.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="fas fa-brain me-2"></i>Face Recognition</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="face_similarity_threshold" class="form-label">Confidence Threshold</label>
                                <input type="number"
                                    class="form-control @error('face_similarity_threshold') is-invalid @enderror"
                                    id="face_similarity_threshold" name="face_similarity_threshold" min="0.10"
                                    max="0.99" step="0.01"
                                    value="{{ old('face_similarity_threshold', number_format($faceThreshold, 2, '.', '')) }}"
                                    required>
                                @error('face_similarity_threshold')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Range 0.10 - 0.99. Semakin tinggi nilai, semakin ketat validasi wajah.
                                </div>
                            </div>

                            <div class="p-3 rounded border bg-light mb-3">
                                <div class="small text-muted mb-1">Current Threshold</div>
                                <div class="h4 mb-0">{{ number_format($faceThreshold * 100, 0) }}%</div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
