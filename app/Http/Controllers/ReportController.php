<?php
namespace App\Http\Controllers;

use App\Models\Complaint;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function complaintsReport(Request $request)
    {
        $admin = auth()->user();
        if (!$admin || $admin->role != 1) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتوليد التقرير',
                'status_code' => 403
            ], 403);
        }

        $complaints = Complaint::with('agency', 'user')->get();

        
$pdf = PDF::loadView('pdf.complaints_report', [
    'complaints' => $complaints,
    'generated_at' => now()->toDateTimeString(),
    'generated_by' => $admin->name,
]);

        return $pdf->download('complaints_report.pdf');
    }
}