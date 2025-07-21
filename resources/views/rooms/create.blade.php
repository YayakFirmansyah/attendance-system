@extends('layouts.app')

@section('title', 'Create Room')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Create New Room</h1>
    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Rooms
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Room Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('rooms.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="room_code" class="form-label">Room Code</label>
                                <input type="text" class="form-control @error('room_code') is-invalid @enderror" 
                                       id="room_code" name="room_code" value="{{ old('room_code') }}" 
                                       placeholder="e.g., A101, LAB-1" required>
                                @error('room_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="room_name" class="form-label">Room Name</label>
                                <input type="text" class="form-control @error('room_name') is-invalid @enderror" 
                                       id="room_name" name="room_name" value="{{ old('room_name') }}" 
                                       placeholder="e.g., Computer Lab, Lecture Hall" required>
                                @error('room_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="building" class="form-label">Building</label>
                                <input type="text" class="form-control @error('building') is-invalid @enderror" 
                                       id="building" name="building" value="{{ old('building') }}" 
                                       placeholder="e.g., Building A">
                                @error('building')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="floor" class="form-label">Floor</label>
                                <input type="text" class="form-control @error('floor') is-invalid @enderror" 
                                       id="floor" name="floor" value="{{ old('floor') }}" 
                                       placeholder="e.g., 1, 2, Ground">
                                @error('floor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                       id="capacity" name="capacity" value="{{ old('capacity') }}" 
                                       min="1" placeholder="e.g., 30" required>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Room Type</label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="classroom" {{ old('type') === 'classroom' ? 'selected' : '' }}>Classroom</option>
                                    <option value="lab" {{ old('type') === 'lab' ? 'selected' : '' }}>Laboratory</option>
                                    <option value="auditorium" {{ old('type') === 'auditorium' ? 'selected' : '' }}>Auditorium</option>
                                    <option value="meeting_room" {{ old('type') === 'meeting_room' ? 'selected' : '' }}>Meeting Room</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="facilities" class="form-label">Facilities</label>
                        <textarea class="form-control @error('facilities') is-invalid @enderror" 
                                  id="facilities" name="facilities" rows="3" 
                                  placeholder="e.g., Projector, AC, WiFi, Whiteboard">{{ old('facilities') }}</textarea>
                        @error('facilities')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('rooms.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection