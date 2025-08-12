<?php

namespace App\Repositories;

use App\Models\ReportOnService;
use App\Models\ReportReason;

class ReportRepository
{
    public function createReport(array $data)
    {
        return ReportOnService::create($data);
    }

    public function getReports(string $reportableType, int $page = 1)
    {
        return ReportOnService::where('reportable_type', $reportableType)
            ->with(['user:id,first_name,last_name', 'reason', 'reportable'])
            ->latest()
            ->simplePaginate(15, ['*'], 'page', $page);
    }
    
    public function getReportReasons(string $type)
    {
        return ReportReason::where('type', $type)->orWhere('type', 'general')->get();
    }
}
