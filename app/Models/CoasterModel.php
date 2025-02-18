<?php

namespace App\Models;

use CodeIgniter\Model;
use Predis\Client;

class CoasterModel
{
    protected $redis;

    public function __construct()
    {
        // Initialize Redis client with the correct host and port
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => 'redis-coasters',  // Use localhost if running on the host machine
            'port'   => 6379,          // Redis default port
        ]);
    }

    public function addCoaster($data)
    {
        $coasterId = uniqid();

        // Ensure the data is properly serialized into a string (JSON)
        $jsonData = json_encode($data);

        // Store the JSON string in Redis under a unique key
        $this->redis->set("coaster:$coasterId", $jsonData);

        return $coasterId;
    }



    public function getCoaster($coasterId)
    {
        return $this->redis->hgetall("coaster:$coasterId");
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
