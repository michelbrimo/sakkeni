<?php

namespace App\Services;

use App\Models\Property;
use App\Models\ServiceProvider;
use App\Repositories\ReportRepository;
use Exception;

class ReportServices
{
    protected $reportRepository;

    public function __construct()
    {
        $this->reportRepository = new ReportRepository();
    }

    public function reportProperty(array $data)
    {
        //Property::findOrFail($data['reportable_id']);

        return $this->reportRepository->createReport([
            'user_id' => $data['user_id'],
            'reportable_id' => $data['reportable_id'],
            'reportable_type' => Property::class,
            'report_reason_id' => $data['report_reason_id'],
            'additional_comments' => $data['additional_comments'] ?? null,
        ]);
    }

    public function reportServiceProvider(array $data)
    {
        // ServiceProvider::findOrFail($data['reportable_id']);

        return $this->reportRepository->createReport([
            'user_id' => $data['user_id'],
            'reportable_id' => $data['reportable_id'],
            'reportable_type' => ServiceProvider::class,
            'report_reason_id' => $data['report_reason_id'],
            'additional_comments' => $data['additional_comments'] ?? null,
        ]);
    }

    public function viewPropertyReports(array $data)
    {
        return $this->reportRepository->getReports(Property::class, $data['page'] ?? 1);
    }

    public function viewServiceProviderReports(array $data)
    {
        return $this->reportRepository->getReports(ServiceProvider::class, $data['page'] ?? 1);
    }

    public function viewPropertyReportReasons()
    {
        return $this->reportRepository->getReportReasons('property');
    }

    public function viewServiceProviderReportReasons()
    {
        return $this->reportRepository->getReportReasons('service_provider');
    }
}
