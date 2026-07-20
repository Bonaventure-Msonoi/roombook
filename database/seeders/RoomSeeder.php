<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            [
                'name' => 'Conference Room A',
                'slug' => 'conference-room-a',
                'description' => 'Large meeting room with projector and whiteboard. Seats up to 12.',
                'capacity' => 12,
                'color' => '#3b82f6',
            ],
            [
                'name' => 'Conference Room B',
                'slug' => 'conference-room-b',
                'description' => 'Medium meeting room ideal for team standups. Seats up to 8.',
                'capacity' => 8,
                'color' => '#10b981',
            ],
            [
                'name' => 'Huddle Room',
                'slug' => 'huddle-room',
                'description' => 'Small private room for quick calls and 1-on-1s. Seats up to 4.',
                'capacity' => 4,
                'color' => '#f59e0b',
            ],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(['slug' => $room['slug']], $room);
        }
    }
}
