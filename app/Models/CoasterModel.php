<?php

namespace App\Models;

use CodeIgniter\Model;
use Predis\Client;

class CoasterModel extends Model
{
    protected $redis;

    public function __construct()
    {
        // Initialize Redis client with the correct host and port
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => 'redis-coasters',  // Use service name here
            'port'   => 6379
        ]);
    }

    public function addCoaster($data)
    {
        $coasterId = uniqid();

        // Ensure the data is properly serialized into a string (JSON)
        $jsonData = json_encode($data);

        // Force delete the key first if it exists
        $this->redis->del("coaster:$coasterId");

        // Store the JSON string in Redis under a unique key
        $this->redis->set("coaster:$coasterId", $jsonData);

        return $coasterId;
    }





    public function getCoasters()
    {
        // Get the main coasters list from Redis
        $coasters = $this->redis->get('coaster');

        // Check if the coasters data exists
        if ($coasters === null) {
            // Handle the case where the coasters list is not found in Redis
            // For example, return a message or default response
            return $this->respond(['error' => 'No coasters found in Redis'], 404);
        }

        // Decode the coasters data if it's found
        $coasters = json_decode($coasters, true);

        // Optionally retrieve all individual coasters if needed
        $individualCoasters = [];
        foreach ($coasters as $coaster) {
            $key = 'coaster:' . $coaster['id'];
            $individualCoaster = $this->redis->get($key);

            if ($individualCoaster) {
                $individualCoasters[] = json_decode($individualCoaster, true);
            }
        }

        // Return the merged results
        return $this->respond(array_merge($coasters, $individualCoasters));
    }

    public function updateCoaster($coasterId, $data)
    {
        // Similar flattening for update
        if (is_array($data)) {
            $flattenedData = [];
            foreach ($data as $key => $value) {
                $flattenedData[(string) $key] = (string) $value;
            }

            $this->redis->hmset("coaster:$coasterId", $flattenedData);
        }
    }

    public function deleteCoaster($coasterId)
    {
        $this->redis->del("coaster:$coasterId");
    }

// ----- Wagons methods -----

    // Add a wagon to a coaster
    public function addWagon($coasterId)
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['ilosc_miejsc']) || !isset($data['predkosc_wagonu'])) {
            return $this->failValidationError('Both "ilosc_miejsc" and "predkosc_wagonu" are required.');
        }

        // You can retrieve the coaster from Redis here and ensure the data is added correctly
        $wagonId = uniqid();  // Generate a unique ID for the wagon

        // Save the wagon data to Redis
        $wagonData = [
            'ilosc_miejsc' => $data['ilosc_miejsc'],
            'predkosc_wagonu' => $data['predkosc_wagonu'],
        ];

        $this->coasterModel->addWagonToCoaster($coasterId, $wagonId, $wagonData);

        return $this->respondCreated([
            'message' => 'Wagon added successfully',
            'wagonId' => $wagonId
        ]);
    }


    // Get all wagons for a coaster
    public function getWagons($coasterId)
    {
        // Get all wagon IDs for the given coaster
        $wagonIds = $this->redis->smembers("coaster:$coasterId:wagons");

        if (empty($wagonIds)) {
            // Return an error if no wagons exist for the coaster
            return $this->respond(['error' => 'No wagons found for this coaster'], 404);
        }

        // Retrieve each individual wagon using the ID
        $wagons = [];
        foreach ($wagonIds as $wagonId) {
            $wagonData = $this->redis->get("coaster:$coasterId:wagon:$wagonId");
            if ($wagonData) {
                $wagons[] = json_decode($wagonData, true);
            }
        }

        return $this->respond($wagons);
    }

    // Update a wagon for a coaster
    public function updateWagon($coasterId, $wagonId, $wagonData)
    {
        // Ensure the wagon data is properly serialized into a string (JSON)
        $jsonWagonData = json_encode($wagonData);

        // Update the wagon data in Redis
        $this->redis->set("coaster:$coasterId:wagon:$wagonId", $jsonWagonData);
    }

    // Delete a specific wagon for a coaster
    public function deleteWagon($coasterId, $wagonId)
    {
        // Delete the individual wagon data
        $this->redis->del("coaster:$coasterId:wagon:$wagonId");

        // Optionally remove the wagon ID from the set of wagons for this coaster
        $this->redis->srem("coaster:$coasterId:wagons", $wagonId);
    }
}
