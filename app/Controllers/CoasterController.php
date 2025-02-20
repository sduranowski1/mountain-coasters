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

    public function failValidationError($message = 'Validation Error')
    {
        return $this->response->setStatusCode(400)->setJSON([
            'error' => $message,
        ]);
    }


    public function getCoasters()
    {
        $coasters = $this->coasterModel->getCoasters();
        return $this->respond($coasters);
    }



    public function createCoaster()
    {
        $data = $this->request->getJSON(true);

        // Validate required fields
        $requiredFields = [
            'liczba_personelu',
            'liczba_klientow',
            'dl_trasy',
            'godziny_od',
            'godziny_do'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return $this->failValidationError(ucfirst(str_replace('_', ' ', $field)) . " is required.");
            }
        }

        // Validate numeric fields (liczba_personelu, liczba_klientow, dl_trasy)
        if (!is_numeric($data['liczba_personelu'])) {
            return $this->failValidationError("Liczba personelu must be a valid number.");
        }
        if (!is_numeric($data['liczba_klientow'])) {
            return $this->failValidationError("Liczba klientow must be a valid number.");
        }
        if (!is_numeric($data['dl_trasy'])) {
            return $this->failValidationError("Dl trasy must be a valid number.");
        }

        // Validate godziny_od and godziny_do format (should be HH:MM)
        if (!preg_match('/^\d{1,2}:\d{2}$/', $data['godziny_od'])) {
            return $this->failValidationError("Godziny od must be in HH:MM format.");
        }
        if (!preg_match('/^\d{1,2}:\d{2}$/', $data['godziny_do'])) {
            return $this->failValidationError("Godziny do must be in HH:MM format.");
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


    public function addWagon($coasterId)
    {
        // Get the JSON data from the request body
        $data = $this->request->getJSON(true);

        // Validate required fields for the wagon
        if (!isset($data['ilosc_miejsc']) || !isset($data['predkosc_wagonu'])) {
            return $this->failValidationError("Both 'ilosc_miejsc' and 'predkosc_wagonu' are required.");
        }

        // Validate 'ilosc_miejsc' (number of seats) - ensure it's a positive integer
        if (!is_numeric($data['ilosc_miejsc']) || $data['ilosc_miejsc'] <= 0) {
            return $this->failValidationError("Ilosc miejsc must be a positive integer.");
        }

        // Validate 'predkosc_wagonu' (wagon speed) - ensure it's a positive number
        if (!is_numeric($data['predkosc_wagonu']) || $data['predkosc_wagonu'] <= 0) {
            return $this->failValidationError("Predkosc wagonu must be a positive number.");
        }

        // Check if the coaster exists
        $coaster = $this->coasterModel->find($coasterId);
        if (!$coaster) {
            return $this->failNotFound("Coaster with ID $coasterId not found.");
        }

        // Prepare the wagon data to insert
        $wagonData = [
            'coaster_id'        => $coasterId,
            'ilosc_miejsc'      => $data['ilosc_miejsc'],
            'predkosc_wagonu'   => $data['predkosc_wagonu']
        ];

        // Assuming you have a WagonModel that handles storing the wagon data
        $wagonModel = new \App\Models\WagonModel();
        $wagonId = $wagonModel->insert($wagonData);

        // Respond with the ID of the newly created wagon
        return $this->respondCreated([
            "message" => "Wagon created successfully",
            "wagon_id" => $wagonId
        ]);
    }



    public function updateCoaster($coasterId)
    {
        $data = $this->request->getJSON(true);
        $this->coasterModel->updateCoaster($coasterId, $data);
        return $this->respond(["message" => "Coaster updated"]);
    }
}
