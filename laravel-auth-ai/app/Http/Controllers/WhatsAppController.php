<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\WaGateway\Services\WaGatewayService;

class WhatsAppController extends Controller
{
    public function __construct(
        protected WaGatewayService $waGatewayService
    ) {}

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'message' => 'required|string|max:5000',
            'url' => 'nullable|url|max:2048',
            'filename' => 'nullable|string|max:255',
            'schedule' => 'nullable|integer|min:0',
            'delay' => ['nullable', 'string', 'max:20', 'regex:/^\d+(-\d+)?$/'],
            'countryCode' => 'nullable|string|max:5',
            'location' => 'nullable|string|max:255',
            'typing' => 'nullable|boolean',
            'choices' => 'nullable|string|max:5000',
            'select' => 'nullable|string|max:5000',
            'pollname' => 'nullable|string|max:255',
            'connectOnly' => 'nullable|boolean',
            'data' => 'nullable|string|max:5000',
            'sequence' => 'nullable|boolean',
            'preview' => 'nullable|boolean',
            'inboxid' => 'nullable|integer|min:1',
            'duration' => 'nullable|integer|min:0|max:86400',
        ]);

        $options = collect($validated)
            ->except(['target', 'message'])
            ->toArray();

        $response = $this->waGatewayService->sendMessage(
            $validated['target'],
            $validated['message'],
            $options
        );

        return response()->json(
            $response,
            ($response['status'] ?? false) ? 200 : 422
        );
    }
}
