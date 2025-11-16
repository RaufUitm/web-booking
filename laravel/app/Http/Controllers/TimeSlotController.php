<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    public function index(Request $request)
    {
        $query = TimeSlot::where('is_available', true);

        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        return $query->orderBy('date')->orderBy('start_time')->get();
    }
}
