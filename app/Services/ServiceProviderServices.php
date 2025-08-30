<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use App\Repositories\ImageRepository;
use App\Repositories\ServiceProviderRepository;
use Exception;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ServiceProviderServices extends ImageServices
{
    protected $service_provider_repository;
    protected $image_repository;

    public function __construct() {
        $this->service_provider_repository = new ServiceProviderRepository();
        $this->image_repository = new ImageRepository();

    }

    function viewServiceCategories() {
        return $this->service_provider_repository->getServiceCategories();
    }
    
    function viewSubscriptionPlans($data) {
        return $this->service_provider_repository->getSubscriptionPlans();
    }

    function viewServiceProviders($data) {
        return $this->service_provider_repository->getServiceProviders($data);
    }

    function viewBestServiceProviders($data) {
        return $this->service_provider_repository->getBestServiceProviders($data);
    }

    function viewServiceProviderDetails($data) {
        return $this->service_provider_repository->getServiceProviderDetails($data['service_provider_id']);
    }

    function viewServiceProviderServiceGallery($data) {
        return $this->service_provider_repository->getServiceProviderServiceGallery($data['service_provider_service_id']);
    }

    function addService($data) {        
        $validator = Validator::make($data, [
            'service_id' => 'required|integer',
            'service_description' => 'string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

        $this->service_provider_repository->createServiceProviderService([
            'service_provider_id' => $data['service_provider_id'],
            'service_id' => $data['service_id'],
            'description' => $data['service_description'],
            'availability_status_id'=> AvailabilityStatus::Pending, 
        ]);
    }

    function viewMyServices($data) {        
        $serviceProviderId = $data['service_provider_id'];
        $serviceProvider = $this->service_provider_repository->getServiceProviderById($serviceProviderId);

        if (!$serviceProvider) {
            throw new Exception('Service provider profile not found.', 404);
        }

        switch ($serviceProvider->status) {
            case 'active':
                return $this->service_provider_repository->getServiceProviderServices($serviceProviderId);
            case 'pending_payment':
                throw new Exception(
                    'Your application is approved. Please complete payment to activate your services.',
                    402
                );
            case 'pending_approval':                
                throw new Exception(
                    'Your application is currently under review by our team.',
                    403
                );
            
            default:
                throw new Exception(
                    'Your account is not active.',
                    403
                );
        }
    }

    function removeService($data) {
        $this->service_provider_repository->deleteServiceProviderService($data['service_provider_service_id']);
    }

    function editService($data) {
        $validator = Validator::make($data, [
            'service_provider_service_id' => 'required|integer',
            'service_gallery' => 'array',
            'description' => 'string'
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

        if(isset($data['description']))
            $this->service_provider_repository->updateService($data['service_provider_service_id'], ['description'=>$data['description']]);

        if(isset($data['service_gallery']))
            $this->updateImages($data['service_provider_service_id'], $data['service_gallery']);
    }


    public function updateImages($service_id, array $newImages)
    {
        $oldImages = $this->image_repository->getImagesByServiceId($service_id);

        foreach ($oldImages as $image) {
            $filePath = str_replace('storage/', '', $image->image_path);
            Storage::disk('public')->delete($filePath);
            $this->image_repository->deleteServiceImage($image->id);
        }

        $this->saveImages($service_id, $newImages);
    }

    protected function saveImages($service_id, $images) {
        $imagesPaths = $this->_storeImages($images, 'services', $service_id);
        $this->image_repository->addServiceImages($service_id, $imagesPaths);
    }



}
