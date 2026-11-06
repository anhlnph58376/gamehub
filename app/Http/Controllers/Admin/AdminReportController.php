<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\GameReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = GameReport::with(['user', 'game', 'review', 'resolver']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $reports = $query->latest()->paginate(15)->withQueryString();

        return view('admin.reports.index', compact('reports'));
    }

    public function resolve(Request $request, GameReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewed,resolved,dismissed'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? $report->admin_note,
            'resolved_by' => auth()->id(),
            'resolved_at' => in_array($validated['status'], ['resolved', 'dismissed'], true) ? now() : null,
        ]);

        AuditLog::record('report.resolve', "Xử lý báo cáo #{$report->id} ({$validated['status']}).", GameReport::class, $report->id);

        return back()->with('success', "Đã cập nhật trạng thái xử lý cho báo cáo #{$report->id}.");
    }
}

