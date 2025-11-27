<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class AlternateVoipTrunkUserPass extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ---- Variáveis configuráveis ----
        $id             = 1;
        $name           = 'brazukatrunk1';
        $usuario        = 'brazukangn';
        $senha          = 'br4zuk4ngn3819j1';
        $endpointHost   = '200.201.211.213'; // Host/IP para originação

        // voip_trunks
        DB::statement("
            INSERT INTO voip_trunks (id, name, codec, auth_type, username, password, host, created_at, updated_at)
            VALUES (?, ?, 'g729,alaw,ulaw', 0, ?, ?, ?, NOW(), NOW())
            ON CONFLICT DO NOTHING
        ", [$id, $name, $usuario, $senha, $endpointHost]);

        // ps_aors
        DB::statement("
            INSERT INTO ps_aors (id, contact, max_contacts)
            VALUES (?, ?, 1)
            ON CONFLICT DO NOTHING
        ", [$name, 'sip:' . $endpointHost . ':5060']);

        // ps_auths
        DB::statement("
            INSERT INTO ps_auths (id, auth_type, username, password)
            VALUES (?, 'userpass', ?, ?)
            ON CONFLICT DO NOTHING
        ", [$name, $usuario, $senha]);

        // ps_endpoints
        DB::statement("
            INSERT INTO ps_endpoints (id, transport, aors, auth, outbound_auth, outbound_proxy, context, disallow, allow)
            VALUES (?, 'transport-udp', ?, ?, ?, 'sip:' || ? || ':5060', 'brazukasbc', 'all', 'g729,alaw,ulaw')
            ON CONFLICT DO NOTHING
        ", [$name, $name, $name, $name, $endpointHost]);
    }
}
