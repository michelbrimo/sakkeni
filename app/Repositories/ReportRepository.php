<?php

namespace App\Repositories;

use App\Models\Property;
use App\Models\ReportOnService;
use App\Models\ReportReason;
use App\Models\ServiceProvider;

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

        $relationships = [
            'user:id,first_name,last_name,email,phone_number', 
            'reason'
        ];

        if ($reportableType === Property::class) {
            $relationships[] = 'reportable.location.country';
            $relationships[] = 'reportable.location.city';
        } elseif ($reportableType === ServiceProvider::class) {
            $relationships[] = 'reportable.user:id,first_name,last_name,email';
        }

        return $query->with($relationships)
            ->latest()
            ->paginate(15, ['*'], 'page', $page);
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
