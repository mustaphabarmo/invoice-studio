<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceBranding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceStudioController extends Controller
{
    public function invoices(Request $request): JsonResponse
    {
        return response()->json(['data' => Invoice::where('user_id', $request->user()->id)->latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->validate(['id' => ['nullable', 'uuid'], 'status' => ['required', 'in:Draft,Final'], 'data' => ['required', 'array'], 'data.invoiceNumber' => ['required', 'string', 'max:100'], 'data.clientName' => ['required', 'string', 'max:255']]);
        $invoice = Invoice::updateOrCreate(
            ['id' => $input['id'] ?? null, 'user_id' => $request->user()->id],
            ['invoice_number' => $input['data']['invoiceNumber'], 'client_name' => $input['data']['clientName'], 'status' => $input['status'], 'data' => $input['data']]
        );
        return response()->json(['data' => $invoice], $invoice->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        abort_unless($invoice->user_id === $request->user()->id, 404);
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted']);
    }

    public function branding(Request $request): JsonResponse
    {
        return response()->json(['data' => InvoiceBranding::where('user_id', $request->user()->id)->value('data')]);
    }

    public function saveBranding(Request $request): JsonResponse
    {
        $input = $request->validate(['data' => ['required', 'array']]);
        $branding = InvoiceBranding::updateOrCreate(['user_id' => $request->user()->id], ['data' => $input['data']]);
        return response()->json(['data' => $branding->data]);
    }
}
