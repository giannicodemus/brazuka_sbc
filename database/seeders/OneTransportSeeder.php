<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class OneTransportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement("
            INSERT INTO ps_transports (
                id,
                protocol,
                bind,
                allow_reload,
                async_operations,
                symmetric_transport
            ) VALUES (
                'transport-udp',            -- ID do transporte
                'udp',                      -- protocolo
                '0.0.0.0:5060',              -- bind address:porta
                'yes',                       -- allow_reload
                1,                           -- async_operations
                'no'                         -- symmetric_transport
            )
            ON CONFLICT (id) DO NOTHING;
        ");
    }
}
