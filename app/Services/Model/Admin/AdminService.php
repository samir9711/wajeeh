<?php

namespace App\Services\Model\Admin;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Admin;
use App\Http\Resources\Model\AdminResource;
use Illuminate\Support\Facades\DB;

class AdminService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Admin::class
        );

        $this->resource = AdminResource::class;
    }


    public function updateProfile(Admin $admin, array $data): Admin
    {
        return DB::transaction(function () use ($admin, $data) {

            
            $admin->name = $data['name'];
            $admin->email = $data['email'];

            if (array_key_exists('image', $data)) {
                $admin->image = $data['image'];
            }


            if (!empty($data['password'])) {
                $admin->password = $data['password'];
            }

            $admin->save();

            return $admin->fresh();
        });
    }
}
