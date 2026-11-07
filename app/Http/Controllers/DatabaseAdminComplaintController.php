<?php

namespace App\Http\Controllers;

use App\Models\ServiceComplaint;
use App\Models\ServiceComplaintAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DatabaseAdminComplaintController extends Controller
{
    private const TRANSITIONS = [
        'submitted' => ['in_review','rejected'],
        'in_review' => ['resolved','rejected'],
        'resolved' => ['in_review'],
        'rejected' => ['in_review'],
    ];

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $query = ServiceComplaint::with(['reporter:id,name','service:id,name','partner:id,business_name','actions.admin:id,name']);
        if (in_array($status, ['submitted','in_review','resolved','rejected'], true)) $query->where('status', $status);
        $complaints = $query->latest()->paginate(15)->withQueryString();
        return view('backend-feedback.complaints.admin', compact('complaints'));
    }

    public function update(Request $request, ServiceComplaint $serviceComplaint): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required','in:submitted,in_review,resolved,rejected'],
            'priority' => ['required','in:normal,high,urgent'],
            'note' => ['required','string','min:10','max:2000'],
        ]);
        DB::transaction(function () use ($serviceComplaint, $request, $data) {
            $complaint = ServiceComplaint::whereKey($serviceComplaint->id)->lockForUpdate()->firstOrFail();
            if ($data['status'] !== $complaint->status &&
                !in_array($data['status'], self::TRANSITIONS[$complaint->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'Perpindahan status pengaduan tidak diperbolehkan.']);
            }
            if ($data['status'] === $complaint->status && $data['priority'] === $complaint->priority) {
                throw ValidationException::withMessages(['status' => 'Ubah status atau prioritas untuk mencatat tindakan.']);
            }
            ServiceComplaintAction::create([
                'service_complaint_id' => $complaint->id,
                'admin_id' => $request->user()->id,
                'from_status' => $complaint->status,
                'to_status' => $data['status'],
                'from_priority' => $complaint->priority,
                'to_priority' => $data['priority'],
                'note' => $data['note'],
            ]);
            $complaint->update([
                'status' => $data['status'],
                'priority' => $data['priority'],
                'resolution_note' => $data['note'],
                'handled_by' => $request->user()->id,
                'closed_at' => in_array($data['status'], ['resolved','rejected'], true) ? now() : null,
            ]);
        });
        return back()->with('success', 'Pengaduan diperbarui dan dicatat dalam log penanganan.');
    }
}
