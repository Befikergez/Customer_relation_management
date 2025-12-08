<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cities = City::with(['subcities'])->latest()->get();
        
        return Inertia::render('Admin/Cities/Index', [
            'cities' => $cities,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Cities/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name',
            'description' => 'nullable|string|max:500',
        ]);

        City::create($validated);

        // Check if this is a request from settings modal
        if ($request->has('from_settings') || str_contains($request->header('Referer'), 'settings')) {
            return redirect()->route('admin.settings')->with('success', 'City created successfully.');
        }

        return redirect()->route('cities.index')->with('success', 'City created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(City $city)
    {
        $city->load(['subcities']);
        
        return Inertia::render('Admin/Cities/Show', [
            'city' => $city,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(City $city)
    {
        return Inertia::render('Admin/Cities/Edit', [
            'city' => $city,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name,' . $city->id,
            'description' => 'nullable|string|max:500',
        ]);

        $city->update($validated);

        // Check if this is a request from settings modal
        if ($request->has('from_settings') || str_contains($request->header('Referer'), 'settings')) {
            return redirect()->route('admin.settings')->with('success', 'City updated successfully.');
        }

        return redirect()->route('cities.index')->with('success', 'City updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(City $city)
    {
        // Check if city has subcities
        if ($city->subcities()->count() > 0) {
            if (request()->has('from_settings') || str_contains(request()->header('Referer'), 'settings')) {
                return redirect()->route('admin.settings')->with('error', 'Cannot delete city that has subcities. Please delete the subcities first.');
            }
            return redirect()->back()->with('error', 'Cannot delete city that has subcities. Please delete the subcities first.');
        }

        $city->delete();

        // Check if this is a request from settings modal
        if (request()->has('from_settings') || str_contains(request()->header('Referer'), 'settings')) {
            return redirect()->route('admin.settings')->with('success', 'City deleted successfully.');
        }

        return redirect()->route('cities.index')->with('success', 'City deleted successfully.');
    }
}