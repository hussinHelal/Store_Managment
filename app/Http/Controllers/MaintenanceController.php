<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $maintenances = Maintenance::all();
        return view('Maintenance.index', compact('maintenances'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Maintenance.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'owner' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'requested_date' => 'required|date',
            'completed_date' => 'nullable|date',
        ]);

        Maintenance::create($request->all());

        return redirect()->route('maintenance.index');
    }

    /**
     * Display the specified resource.
     */
    // public function show(Maintenance $maintenance)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Maintenance $maintenance)
    {
        return view('Maintenance.edit', compact('maintenance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Maintenance $maintenance)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'owner' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',            
            'requested_date' => 'required|date',
            'completed_date' => 'nullable|date',
        ]);

        $maintenance->update($request->all());

        return redirect()->route('maintenance.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Maintenance $maintenance)
    {
        $maintenanceId = Maintenance::findOrFail($maintenance->id);
        $maintenanceId->delete();

        return redirect()->route('maintenance.index');
    }

    public function repair(Maintenance $maintenance)
    {
        $maintenance->update([
            'status' => 'مكتمل',
            'completed_date' => now(),
        ]);

        return redirect()->route('maintenance.index');
    }
}
