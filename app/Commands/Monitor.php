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
                $coaster = $redis->hgetall($coasterKey);
                CLI::write("Coaster: " . $coasterKey, 'green');
                CLI::write("  - Staff: " . $coaster['liczba_personelu']);
                CLI::write("  - Daily Clients: " . $coaster['liczba_klientow']);
                CLI::write("  - Status: OK\n", 'green');
            }

            sleep(5); // Update every 5 seconds
        }
    }
}
