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
}
