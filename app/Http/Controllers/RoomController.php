<?php
// app/Http/Controllers/RoomController.php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('role:admin');
    // }

    public function index()
    {
        $rooms = Room::orderBy('room_code')->paginate(15);
        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_code' => 'required|unique:rooms',
            'room_name' => 'required|max:255',
            'building' => 'nullable|max:100',
            'floor' => 'nullable|max:10',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|in:classroom,lab,auditorium,meeting_room',
            'facilities' => 'nullable',
            'status' => 'required|in:active,inactive,maintenance'
        ]);

        Room::create($validated);
        return redirect()->route('rooms.index')->with('success', 'Room created successfully');
    }

    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_code' => 'required|unique:rooms,room_code,' . $room->id,
            'room_name' => 'required|max:255',
            'building' => 'nullable|max:100',
            'floor' => 'nullable|max:10',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|in:classroom,lab,auditorium,meeting_room',
            'facilities' => 'nullable',
            'status' => 'required|in:active,inactive,maintenance'
        ]);

        $room->update($validated);
        return redirect()->route('rooms.index')->with('success', 'Room updated successfully');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully');
    }
}