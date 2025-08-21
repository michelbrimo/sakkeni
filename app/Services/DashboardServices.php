<?php

namespace App\Services;

use App\Repositories\DashboardRepository;

class DashboardServices 
{
    protected $dashboard_repository;

    public function __construct() {
        $this->dashboard_repository = new DashboardRepository();
    }

    function viewTotalUsers() {
        return $this->dashboard_repository->getUserStats();
    }

    function viewTotalProperties() {
        return $this->dashboard_repository->getPropertyStats();
    }

    function viewPropertiesStatus() {
        return $this->dashboard_repository->getPropertyStatusStats();
    }

    function viewServiceStatus() {
        return $this->dashboard_repository->getServiceStatusStats();
    }

    function viewPropertiesLocation() {
        return  $this->dashboard_repository->getPropertiesLocations();
    }
}
