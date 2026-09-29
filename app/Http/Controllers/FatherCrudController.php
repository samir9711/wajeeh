<?php

namespace App\Http\Controllers;

use App\Http\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class FatherCrudController extends Controller
{
    use GeneralTrait;
    protected $key;
    protected $service;

    protected $createRequest;
    protected $updateRequest;

    public function __construct()
    {

        $this->setVariables();

    }

    protected abstract function setVariables(): void;

    public function allPaginated(Request $request)
    {

        try {

            return $this->apiResponse(
                $this->service::allPaginated($request)
            );

        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }

    public function pureAll(Request $request)
    {
        try {

            $data[Str::plural($this->key)] = $this->service::pureAll($request);
            return $this->apiResponse(
                $data
            );

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function all(Request $request)
    {

        try {

            $data[Str::plural($this->key)] = $this->service::all($request);
            return $this->apiResponse(
                $data
            );

        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }

    public function show(Request $request)
    {

        try {

            $data[$this->key] = $this->service::show($request);
            return $this->apiResponse($data);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }


    public function store()
    {

        try {

            $request = app($this->createRequest);
            $data[$this->key] = $this->service::create($request);

            return $this->apiResponse($data);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }

    public function update()
    {

        try {

            $request = app($this->updateRequest);
            $data[$this->key] = $this->service::update($request);

            return $this->apiResponse($data);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }

    public function activate(Request $request)
    {

        try {

            $data["message"] = ($this->service::activate($request)) ? "activation done"
                : "there was an error with activate data";
            return $this->apiResponse($data);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }

    public function deactivate(Request $request)
    {

        try {

            $data["message"] = ($this->service::deactivate($request)) ? "deactivation done"
                : "there was an error with deactivate data";
            return $this->apiResponse($data);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }

    public function destroy(Request $request)
    {
        try {

            $data["message"] = ($this->service::delete($request)) ? "deletion done"
                : "there was an error with delete data";
            return $this->apiResponse($data);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function exportExcel(Request $request)
    {
        try {

            $data["file"] = $this->service::exportExcel($request);
            return $this->apiResponse($data);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function importExcel(Request $request)
    {
        try {

            $data["message"] = ($this->service::importExcel($request)) ? "import done"
                : "there was an error with import data";
            return $this->apiResponse($data);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function forAuth(Request $request)
    {
        try {
            $data[Str::plural($this->key)] = $this->service::forAuth($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


}
