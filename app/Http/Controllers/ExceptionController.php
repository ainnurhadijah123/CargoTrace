<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\Request;
use Exception;

class ExceptionController extends Controller
{
    protected ExceptionService $exceptionService;

    public function __construct(ExceptionService $exceptionService)
    {
        $this->exceptionService = $exceptionService;
    }

    public function store(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'type'        => 'required|string',
            'severity'    => 'required|in:LOW,MEDIUM,HIGH',
            'description' => 'required|string|max:500',
        ]);

        try {
            $this->exceptionService->reportException($shipment, $validated, auth()->id());
            return back()->with('success', 'Shipment exception reported successfully. Status updated to EXCEPTION.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function resolve(Request $request, $id)
    {
        $exception = ShipmentException::findOrFail($id);

        try {
            $this->exceptionService->resolveException($exception, $request->notes ?? 'Resolved by operations team.', auth()->id());
            return back()->with('success', 'Exception marked as RESOLVED. Transit status updated.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
