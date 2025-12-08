<?php

namespace App\Http\Controllers;

use App\Models\Subcity;
use App\Models\City;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubcityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subcities = Subcity::with(['city'])->latest()->get();
        
        return Inertia::render('Admin/Subcities/Index', [
            'subcities' => $subcities,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cities = City::all();
        
        return Inertia::render('Admin/Subcities/Create', [
            'cities' => $cities,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        // Check for unique name within the same city
        $exists = Subcity::where('city_id', $validated['city_id'])
                        ->where('name', $validated['name'])
                        ->exists();
                        
        if ($exists) {
            if ($request->has('from_settings') || str_contains($request->header('Referer'), 'settings')) {
                return redirect()->route('admin.settings')->withErrors(['name' => 'A subcity with this name already exists in the selected city.']);
            }
            return redirect()->back()->withErrors(['name' => 'A subcity with this name already exists in the selected city.']);
        }

        Subcity::create($validated);

        // Check if this is a request from settings modal
        if ($request->has('from_settings') || str_contains($request->header('Referer'), 'settings')) {
            return redirect()->route('admin.settings')->with('success', 'Subcity created successfully.');
        }

        return redirect()->route('subcities.index')->with('success', 'Subcity created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subcity $subcity)
    {
        $subcity->load(['city']);
        
        return Inertia::render('Admin/Subcities/Show', [
            'subcity' => $subcity,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subcity $subcity)
    {
        $cities = City::all();
        
        return Inertia::render('Admin/Subcities/Edit', [
            'subcity' => $subcity,
            'cities' => $cities,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subcity $subcity)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255|unique:subcities,name,' . $subcity->id . ',id,city_id,' . $request->city_id,
            'description' => 'nullable|string|max:500',
        ]);

        $subcity->update($validated);

        // Check if this is a request from settings modal
        if ($request->has('from_settings') || str_contains($request->header('Referer'), 'settings')) {
            return redirect()->route('admin.settings')->with('success', 'Subcity updated successfully.');
        }

        return redirect()->route('subcities.index')->with('success', 'Subcity updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subcity $subcity)
    {
        $subcity->delete();

        // Check if this is a request from settings modal
        if (request()->has('from_settings') || str_contains(request()->header('Referer'), 'settings')) {
            return redirect()->route('admin.settings')->with('success', 'Subcity deleted successfully.');
        }

        return redirect()->route('subcities.index')->with('success', 'Subcity deleted successfully.');
    }
}