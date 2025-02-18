<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CoasterModel;

class CoasterController extends ResourceController
{
    protected $coasterModel;

    public function __construct()
    {
        $this->coasterModel = new CoasterModel();
    }

    public function getCoasters()
    {
        $coasters = $this->coasterModel->getCoasters();
        return $this->respond($coasters);
    }



    public function createCoaster()
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['name'])) {
            return $this->failValidationError("Coaster name is required.");
        }

        // Debug: Print data structure
        log_message('debug', 'Received coaster data: ' . print_r($data, true));

        // Make sure data is properly formatted
        if (is_array($data)) {
            // Optionally sanitize or process the data if needed
            $id = $this->coasterModel->addCoaster($data);
        } else {
            return $this->failValidationError("Invalid data format.");
        }

        return $this->respondCreated([
            "message" => "Coaster created successfully",
            "id" => $id
        ]);
    }




    public function updateCoaster($coasterId)
    {
        $data = $this->request->getJSON(true);
        $this->coasterModel->updateCoaster($coasterId, $data);
        return $this->respond(["message" => "Coaster updated"]);
    }
}
