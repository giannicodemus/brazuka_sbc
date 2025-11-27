<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class AlternateVoipTrunkIpTech extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ---- Variáveis configuráveis ----
        $id             = 2;
        $name           = 'brazukatrunk2';
        $ipAuth         = '186.209.116.61'; // IP de autenticação (para inbound match)
        $endpointHost   = '186.209.116.61'; // Host/IP para originação
        $techprefix     = '6006+';

        // voip_trunks
        DB::statement("
            INSERT INTO voip_trunks (id, name, codec, auth_type, host, techprefix, created_at, updated_at)
            VALUES (?, ?, 'g729,alaw,ulaw', 1, ?, ?, NOW(), NOW())
            ON CONFLICT DO NOTHING
        ", [$id, $name, $ipAuth, $techprefix]);

        // ps_aors (com contact para envio)
        DB::statement("
            INSERT INTO ps_aors (id, contact, max_contacts)
            VALUES (?, ?, 1)
            ON CONFLICT DO NOTHING
        ", [$name, 'sip:' . $endpointHost . ':5060']);

        // ps_endpoints
        DB::statement("
            INSERT INTO ps_endpoints (id, transport, aors, outbound_proxy, context, disallow, allow)
            VALUES (?, 'transport-udp', ?, 'sip:' || ? || ':5060', 'brazukasbc', 'all', 'g729,alaw,ulaw')
            ON CONFLICT DO NOTHING
        ", [$name, $name, $endpointHost]);

        // ps_endpoint_id_ips (match para inbound pelo IP de autenticação)
        DB::statement("
            INSERT INTO ps_endpoint_id_ips (id, endpoint, match)
            VALUES (?, ?, ?)
            ON CONFLICT DO NOTHING
        ", [$name, $name, $ipAuth]);
    }
}
