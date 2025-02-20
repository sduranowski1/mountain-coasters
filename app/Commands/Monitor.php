<?php


namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Predis\Client;

class Monitor extends BaseCommand
{
    protected $group = 'custom';
    protected $name = 'monitor:coasters';
    protected $description = 'Monitor coaster system in real time.';

    public function run(array $params)
    {
        $redis = new Client();
        while (true) {
            $coasters = $redis->keys("coaster:*");
            CLI::write("---- Roller Coaster Status ----", 'yellow');

            foreach ($coasters as $coasterKey) {
                // Retrieve the coaster data as a string (e.g., JSON)
                $coasterData = $redis->get($coasterKey);

                // If the coaster data exists and is a valid JSON string
                if ($coasterData) {
                    // Decode the JSON data to an array
                    $coaster = json_decode($coasterData, true);

                    // Ensure the data is decoded properly and is an array
                    if (is_array($coaster)) {
                        CLI::write("Coaster: " . $coasterKey, 'green');

                        // Output data available in the JSON
//                        CLI::write("  - Name: " . $coaster['name']);
//                        CLI::write("  - Height: " . $coaster['height']);
//                        CLI::write("  - Speed: " . $coaster['speed']);
//                        CLI::write("  - Location: " . $coaster['location']);

                        // Now output the new fields
                        CLI::write("  - Staff: " . ($coaster['liczba_personelu'] ?? 'Not available'));
                        CLI::write("  - Daily Clients: " . ($coaster['liczba_klientow'] ?? 'Not available'));
                        CLI::write("  - Track Length: " . ($coaster['dl_trasy'] ?? 'Not available'));
                        CLI::write("  - Open Hours: " . ($coaster['godziny_od'] ?? 'Not available') . " - " . ($coaster['godziny_do'] ?? 'Not available'));

                        CLI::write("  - Status: OK\n", 'green');
                    } else {
                        CLI::write("  - Invalid data format for coaster: " . $coasterKey, 'red');
                    }
                } else {
                    CLI::write("  - No data for coaster: " . $coasterKey, 'red');
                }
            }

            sleep(5); // Update every 5 seconds
        }
    }


}
