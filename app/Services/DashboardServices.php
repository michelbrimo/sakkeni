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


}
