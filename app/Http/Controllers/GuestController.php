<?php

namespace App\Http\Controllers;

use App\Services\QueuePerformanceReportService;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    //

    public function generateAWTBookingReportPdf()
    {
        // Logic to generate AWT booking report PDF
        return view('reports.booking.awt_booking_pdf');
    }

    public function generateTATBookingReportPdf()
    {
        // Logic to generate TAT booking report PDF
        return view('reports.booking.tat_booking_pdf');

    }

    public function generateALLBookingReportPdf(Request $request, QueuePerformanceReportService $report)
    {
        $filters = [
            'period_start' => $request->query('period_start'),
            'period_end' => $request->query('period_end'),
            'doctor_id' => $request->query('doctor_id'),
            'service_id' => $request->query('service_id'),
            'room_id' => $request->query('room_id'),
        ];

        $result = $report->build($filters);

        return view('reports.booking.all_booking_pdf', [
            'clinicName' => config('app.name'),
            'title' => 'Laporan & Rekap Kinerja Antrean',
            'reportCode' => 'QUEUE-PERF',
            'generatedAt' => $result['generatedAt'],
            'periodStart' => $result['periodStart'],
            'periodEnd' => $result['periodEnd'],
            'filters' => $filters,
            'summary' => $result['summary'],
            'daily' => $result['daily'],
            'priorityBreakdown' => $result['priorityBreakdown'],
        ]);

    }
}
