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

   
    public function getReports(string $reportableType, string $status, int $page = 1)
    {
        $query = ReportOnService::where('reportable_type', $reportableType);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->with(['user:id,first_name,last_name', 'reason', 'reportable'])
            ->latest()
            ->simplePaginate(15, ['*'], 'page', $page);
    }
    
    public function getReportReasons(string $type)
    {
        return ReportReason::where('type', $type)->orWhere('type', 'general')->get();
    }

    public function processReport(int $reportId, array $data)
    {
        return ReportOnService::where('id', $reportId)->update($data);
    }
}
