<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function index()
    {
        $reports = Report::with(['user', 'post'])->latest()->paginate(10);
        return view('admin.reports.report_form', compact('reports'));
    }
    public function report_show($id)
    {
        $report = Report::with(['user', 'post'])->findOrFail($id);
        return view('admin.reports.report_show', compact('report'));
    }
    public function dismiss($id)
    {
        $report = Report::findOrFail($id);
        $report->status = 'resolved';
        $report->save();

        return redirect()->route('admin.reports.index')->with('success', 'Đã bỏ qua báo cáo vi phạm.');
    }

    public function delete_post($id)
    {
        $report = Report::with('post')->findOrFail($id);

        if ($report->post) {
            $report->post->delete();
        }

        $report->status = 'resolved';
        $report->save();

        return redirect()->route('admin.reports.index')->with('success', 'Đã xóa bài viết vi phạm và đóng báo cáo.');
    }
}
