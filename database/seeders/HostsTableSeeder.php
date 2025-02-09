<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Host;

class HostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Host::create([
            'Model' => 'LB06HUmniah_1G',
            'HostName' => 'HostName',
            'IPAddress' => 'IPAddress',
            'MACAddress' => 'MACAddress',
            'RSSI' => 'RSSI',
            'hostPath' => 'InternetGatewayDevice.LANDevice.1.Hosts.Host',
        ]);
    }
}
