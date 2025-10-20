<?php

namespace App\Http\Controllers;

use App\Models\Events;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\EventsResource;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->has('search')) {
            $search = $request->input('search');
            $events = Events::search($search)->paginate(10);
        } else {
            $events = Events::paginate(10);
        }

        return response()->json([
            'message' => 'success',
            'data' => EventsResource::collection($events),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'per_page' => $events->perPage(),
            'total' => $events->total(),
            'links' => $events->linkCollection(),
            'next_page_url' => $events->nextPageUrl(),
            'prev_page_url' => $events->previousPageUrl(),
            'error' => null
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Events::class);
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'location' => 'required',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);

        try {

            $event = Events::create($validated);

            return response()->json([
                'message' => 'success',
                'data' => new EventsResource($event),
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
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $event = Events::where('slug', $slug)->first();

        return response()->json([
            'message' => 'success',
            'data' => new EventsResource($event),
            'error' => null
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $slug)
    {
        $event = Events::where('slug', $slug)->first();

        // Gate::authorize('update', $event);
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'location' => 'required',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);

        try {


            $event->update($validated);

            return response()->json([
                'message' => 'success',
                'data' => new EventsResource($event),
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
    public function destroy(string $slug)
    {

        $event = Events::where('slug', $slug)->first();
        Gate::authorize('destroy', $event);
        $event->delete();

        return response()->json([
            'message' => 'success',
            'data' => null,
            'error' => null
        ], 200);
    }
}
