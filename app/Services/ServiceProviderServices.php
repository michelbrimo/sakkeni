<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use App\Repositories\ServiceProviderRepository;
use Exception;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ServiceProviderServices extends ImageServices
{
    protected $service_provider_repository;

    public function __construct() {
        $this->service_provider_repository = new ServiceProviderRepository();
    }

    function viewServiceCategories() {
        return $this->service_provider_repository->getServiceCategories();
    }

    function viewServiceProviders($data) {
        return $this->service_provider_repository->getServiceProviders($data);
    }

    function viewServiceProviderDetails($data) {
        return $this->service_provider_repository->getServiceProviderDetails($data['service_provider_id']);
    }

    function viewServiceProviderServiceGallery($data) {
        return $this->service_provider_repository->getServiceProviderServiceGallery($data['service_provider_service_id']);
    }
}
