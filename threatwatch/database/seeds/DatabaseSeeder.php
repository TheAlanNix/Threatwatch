<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        #Model::unguard();

		DB::table('blacklists')->insert([
            'name'			=> 'Malc0de.com IP Blacklist',
            'url'			=> 'https://malc0de.com/bl/IP_Blacklist.txt',
            'regex'			=> '/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/',
            'created_at'	=> \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at'	=> \Carbon\Carbon::now()->toDateTimeString(),
        ]);

        DB::table('blacklists')->insert([
	        'name'			=> 'Binary Defense Banlist',
            'url'			=> 'http://www.binarydefense.com/banlist.txt',
            'regex'			=> '/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/',
            'created_at'	=> \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at'	=> \Carbon\Carbon::now()->toDateTimeString(),
        ]);

		DB::table('blacklists')->insert([
	        'name'			=> 'Emerging Threats - Compromised IPs',
            'url'			=> 'http://rules.emergingthreats.net/blockrules/compromised-ips.txt',
            'regex'			=> '/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/',
            'created_at'	=> \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at'	=> \Carbon\Carbon::now()->toDateTimeString(),
        ]);

		DB::table('blacklists')->insert([
	        'name'			=> 'abuse.ch Zeus Tracker Blocklist',
            'url'			=> 'https://zeustracker.abuse.ch/blocklist.php?download=ipblocklist',
            'regex'			=> '/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/',
            'created_at'	=> \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at'	=> \Carbon\Carbon::now()->toDateTimeString(),
        ]);
    }
}
