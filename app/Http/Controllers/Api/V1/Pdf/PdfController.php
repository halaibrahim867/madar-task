<?php

namespace App\Http\Controllers\Api\V1\Pdf;

use App\Http\Controllers\Controller;
use App\Services\Api\V1\PDF\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PdfController extends Controller
{
    public function __construct(private PdfService $service) {}

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        try {
            $pdfFile = $this->service->process($request->user(), $request->file('file'));

            return response()->json([
                'message' => 'PDF uploaded and indexed successfully',
                'pdf_id' => $pdfFile->id,
                'chunks' => $pdfFile->chunks()->count(),
            ], 201);

        } catch (\Throwable $e) {
            Log::error('PDF upload failed', ['exception' => $e]);

            return response()->json([
                'message' => 'Failed to process PDF',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
