<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Guests;
use Illuminate\Support\Str;
use App\Events\GuestCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\GuestsResource;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Vinkla\Hashids\Facades\Hashids;

class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->has('search')){
            $guests = Guests::search($request->input('search'))->paginate(10);
        }else {
            $guests = Guests::paginate(10);
        }

        return response()->json([
            'message' => 'success',
            'data' => GuestsResource::collection($guests),
            'current_page' => $guests->currentPage(),
            'last_page' => $guests->lastPage(),
            'per_page' => $guests->perPage(),
            'total' => $guests->total(),
            'links' => $guests->linkCollection(),
            'next_page_url' => $guests->nextPageUrl(),
            'prev_page_url' => $guests->previousPageUrl(),
            'error' => null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'event_id' => 'required|exists:events,id|',
            'email' => 'required|email|unique:guests,email',
            'phone' => 'required|numeric|min:10',
            'organization' => 'nullable',
            'status' => 'required|in:invited,checked_in,cancelled',
            'check_in_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable',
        ]);

        try {
            $validated['code'] = 'invt-' . Str::uuid();
            // $validated['qr_generated'] = QrCode::size(300)->generate($validated['code']);
            $validated['created_by'] = Auth::user()->id;

            $guest = Guests::create($validated);

            event(new GuestCreated($guest));

            return response()->json([
                'message' => 'success',
                'data' => new GuestsResource($guest),
                'error' => null
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'error',
                'data' => null,
                'error' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $guest = Guests::find($id);

        return response()->json([
            'message' => 'success',
            'data' => new GuestsResource($guest),
            'error' => null
        ], 200);
    }
    public function checkIn(Request $request, $event_id)
    {
        $validated = $request->validate([
            'code' => 'required',
        ]);
        $guest = Guests::where('event_id', $event_id)->where('code', $validated['code'])->first();

        if (!$guest) {
            return response()->json([
                'message' => 'error',
                'data' => null,
                'error' => 'Guest not found'
            ], 200);
        }

        if ($guest->status === 'cancelled') {
            return response()->json([
                'message' => 'error',
                'data' => null,
                'error' => 'Guest has been cancelled'
            ], 200);
        }

        if ($guest->qr_generated  === null) {
            return response()->json([
                'message' => 'error',
                'data' => new GuestsResource($guest),
                'error' => 'Guest not confirmed attendance'
            ], 200);
        }
        if ($guest->status === 'checked_in') {
            return response()->json([
                'message' => 'error',
                'data' => new GuestsResource($guest),
                'error' => 'Guest has already checked in'
            ], 200);
        }

        // $guest->update(['status' => 'checked_in', 'check_in_time' => now()]);

        return response()->json([
            'message' => 'success checked in',
            'data' => new GuestsResource($guest),
            'error' => null
        ], 200);
    }

    public function listCheckIn($event_id){
        $guests = Guests::where('event_id', $event_id)->where('status', 'checked_in')->get();

        return response()->json([
            'message' => 'success',
            'data' => GuestsResource::collection($guests),
            'error' => null
        ], 200);
    }

    public function confirmAttendance(Request $request, $id)
    {
        $validated = $request->validate([
            'available_date' => 'required|date_format:Y-m-d',
            'status' => 'required|in:confirmed,cancelled',
        ]);

        try {
            $decodedId = Hashids::decode($id);
            $guest = Guests::where('id', $decodedId)->first();

            $available_date = Carbon::parse($validated['available_date']);
            if (!$available_date->between($guest->event->start_date, $guest->event->end_date, true)) {
                return response()->json([
                    'message' => 'Tanggal kehadiran harus di antara tanggal mulai dan berakhirnya event',
                    'data' => null,
                    'error' => 'invalid_date_range'
                ], 422);
            }

            // Perlu ditambahkan untuk notifikasi email dan pada isi dari email ada penambahan event pada calender google
            $guest['qr_generated'] = QrCode::size(300)->generate($guest['code']);
            $guest['available_date'] = $validated['available_date'];
            $guest['status'] = $validated['status'];

            $guest->update($validated);

            return response()->json([
                'message' => 'Success Cofirmed attendance',
                'data' => new GuestsResource($guest),
                'error' => null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'error',
                'data' => null,
                'error' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            'event_id' => 'required|exists:events,id|',
            'email' => 'required|email|unique:guests,email,' . $id,
            'phone' => 'required|numeric|min:10',
            'organization' => 'nullable',
            'status' => 'required|in:invited,checked_in,cancelled',
            'check_in_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable',
        ]);

        try {
            $guest = Guests::findOrFail($id);
            $guest->update($validated);

            return response()->json([
                'message' => 'success',
                'data' => new GuestsResource($guest),
                'error' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'error',
                'data' => null,
                'error' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $guest = Guests::findOrFail($id);
        $guest->delete();

        return response()->json([
            'message' => 'success',
            'data' => null,
            'error' => null
        ], 200);
    }
}
