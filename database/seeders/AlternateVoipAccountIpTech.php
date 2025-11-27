<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class AlternateVoipAccountIpTech extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Variáveis para trocar facilmente
        $id         = 3;
        $ip         = '186.209.116.38';
        $usuario = 'brazukangn3';
        $techprefix = '6006#';

        // Inserir na voip_accounts
        DB::statement("
            INSERT INTO voip_accounts (id, name, codec, auth_type, host, techprefix, created_at, updated_at)
            VALUES (:id, :name, 'g729,alaw,ulaw', 1, :host, :techprefix, NOW(), NOW())
            ON CONFLICT (id) DO NOTHING
        ", [
            'id'         => $id,
            'name'       => 'Conta default por IP',
            'host'       => $ip,
            'techprefix' => $techprefix
        ]);

        // Inserir no ps_aors (sem auth pois é IP)
        DB::statement("
            INSERT INTO ps_aors (id, max_contacts)
            VALUES (:id, 1)
            ON CONFLICT (id) DO NOTHING
        ", ['id' => $usuario]);

        // Inserir no ps_endpoints
        DB::statement("
            INSERT INTO ps_endpoints (id, transport, aors, context, disallow, allow)
            VALUES (:id, 'transport-udp', :aors, 'brazukasbc', 'all', 'g729,alaw,ulaw')
            ON CONFLICT (id) DO NOTHING
        ", [
            'id'   => $usuario,
            'aors' => $usuario
        ]);

        // Inserir no ps_endpoint_id_ips para vincular IP
        DB::statement("
            INSERT INTO ps_endpoint_id_ips (id, endpoint, match)
            VALUES (:id, :endpoint, :match)
            ON CONFLICT (id) DO NOTHING
        ", [
            'id'       => $usuario,
            'endpoint' => $usuario,
            'match'    => $ip
        ]);
    }
}
