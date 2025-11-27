<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class AlternateVoipAccountUserPass extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Variáveis para trocar facilmente
         $id      = 1;
         $name = 'Conta default - brazukangn';
         $usuario = 'brazukangn1';
         $senha   = '29309akjf0am1mq0ajan';
 
         // Inserir na voip_accounts
         DB::statement("
             INSERT INTO voip_accounts (id, name, codec, auth_type, username, password, created_at, updated_at)
             VALUES (:id, :name, 'g729,alaw,ulaw', 0, :username, :password, NOW(), NOW())
             ON CONFLICT (id) DO NOTHING
         ", [
             'id'       => $id,
             'name'     => $name,
             'username' => $usuario,
             'password' => $senha
         ]);
 
         // Inserir no ps_aors
         DB::statement("
             INSERT INTO ps_aors (id, max_contacts)
             VALUES (:id, 1)
             ON CONFLICT (id) DO NOTHING
         ", ['id' => $usuario]);
 
         // Inserir no ps_auths
         DB::statement("
             INSERT INTO ps_auths (id, auth_type, username, password)
             VALUES (:id, 'userpass', :username, :password)
             ON CONFLICT (id) DO NOTHING
         ", [
             'id'       => $usuario,
             'username' => $usuario,
             'password' => $senha
         ]);
 
         // Inserir no ps_endpoints
         DB::statement("
             INSERT INTO ps_endpoints (id, transport, aors, auth, context, disallow, allow)
             VALUES (:id, 'transport-udp', :aors, :auth, 'brazukasbc', 'all', 'g729,alaw,ulaw')
             ON CONFLICT (id) DO NOTHING
         ", [
             'id'   => $usuario,
             'aors' => $usuario,
             'auth' => $usuario
         ]);
    }
}
