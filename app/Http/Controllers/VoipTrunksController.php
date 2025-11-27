<?php

namespace App\Http\Controllers;

use App\Models\VoipTrunk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
class VoipTrunksController extends Controller
{
    public function index(Request $request): Response
    {
        $trunks = VoipTrunk::orderBy('id', 'asc')->get();
        $id = VoipTrunk::max('id');
        $lastId = $id + 1;
        $password = Str::random(12);

        return Inertia::render('VoipTrunks/Index', [
            'trunks' => $trunks,
            'lastId' => $lastId,
            'password' => $password
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'codec' => 'nullable|string|max:255',
            'auth_type' => 'required|in:0,1',
            'username' => 'required_if:auth_type,0|string|nullable',
            'password' => 'required_if:auth_type,0|string|nullable',
            'host' => 'required_if:auth_type,1|string|nullable',
            'techprefix' => 'required_if:auth_type,1|string|nullable',
            'removedigits' => 'nullable|integer',
        ]);

        $id = $data['id'] ?? (DB::table('voip_trunks')->max('id') + 1);
        $codec = $data['codec'] ?: 'g729,alaw,ulaw';

        if ($data['auth_type'] == '0') {
            DB::transaction(function () use ($id, $data, $codec) {
                DB::statement("
                INSERT INTO voip_trunks (id, name, codec, auth_type, username, password, host, removedigits, created_at, updated_at)
                VALUES (:id, :name, :codec, 0, :username, :password, :host, :removedigits, NOW(), NOW())
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $id,
                    'name' => $data['name'],
                    'codec' => $codec,
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'host' => $data['host'],
                    'removedigits' => $data['removedigits'] ?? '0',
                ]);

                DB::statement("
                INSERT INTO ps_aors (id, contact, max_contacts)
                VALUES (:id, :contact, 1)
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $data['name'],
                    'contact' => 'sip:' . $data['host'] . ':5060',
                ]);

                DB::statement("
                INSERT INTO ps_auths (id, auth_type, username, password)
                VALUES (:id, 'userpass', :username, :password)
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $data['name'],
                    'username' => $data['username'],
                    'password' => $data['password'],
                ]);

                DB::statement("
                INSERT INTO ps_endpoints (id, transport, aors, auth, outbound_auth, outbound_proxy, context, disallow, allow)
                VALUES (:id, 'transport-udp', :aors, :auth, :outbound_auth, 'sip:' || :host || ':5060', 'brazukasbc', 'all', :allow)
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $data['name'],
                    'aors' => $data['name'],
                    'auth' => $data['name'],
                    'outbound_auth' => $data['name'],
                    'host' => $data['host'],
                    'allow' => $codec,
                ]);
            });

        } else {
            DB::transaction(function () use ($id, $data, $codec) {
                DB::statement("
                INSERT INTO voip_trunks (id, name, codec, auth_type, host, techprefix, removedigits, created_at, updated_at)
                VALUES (:id, :name, :codec, 1, :host, :techprefix, :removedigits, NOW(), NOW())
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $id,
                    'name' => $data['name'],
                    'codec' => $codec,
                    'host' => $data['host'],
                    'techprefix' => $data['techprefix'],
                    'removedigits' => $data['removedigits'] ?? '0',
                ]);

                DB::statement("
                INSERT INTO ps_aors (id, contact, max_contacts)
                VALUES (:id, :contact, 1)
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $data['name'],
                    'contact' => 'sip:' . $data['host'] . ':5060',
                ]);

                DB::statement("
                INSERT INTO ps_endpoints (id, transport, aors, outbound_proxy, context, disallow, allow)
                VALUES (:id, 'transport-udp', :aors, 'sip:' || :host || ':5060', 'brazukasbc', 'all', :allow)
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $data['name'],
                    'aors' => $data['name'],
                    'host' => $data['host'],
                    'allow' => $codec,
                ]);

                DB::statement("
                INSERT INTO ps_endpoint_id_ips (id, endpoint, match)
                VALUES (:id, :endpoint, :match)
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $data['name'],
                    'endpoint' => $data['name'],
                    'match' => $data['host'],
                ]);
            });
        }

        return response()->json(['success' => 'Trunk criado com sucesso']);
    }

    public function edit($id)
    {

        $trunks = VoipTrunk::where('id', $id)->first();

        return response()->json([
            'trunks' => $trunks,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'codec' => 'nullable|string|max:255',
            'auth_type' => 'required|in:0,1',
            'username' => 'required_if:auth_type,0|string|nullable',
            'password' => 'required_if:auth_type,0|string|nullable',
            'host' => 'required_if:auth_type,1|string|nullable',
            'techprefix' => 'required_if:auth_type,1|string|nullable',
            'removedigits' => 'nullable|integer'
        ]);

        $codec = $data['codec'] ?: 'g729,alaw,ulaw';

        if ($data['auth_type'] == '0') {
            $host = null;
            $techprefix = null;

            DB::transaction(function () use ($id, $data, $codec, $host, $techprefix) {
                DB::statement("
                UPDATE voip_trunks
                SET name = :name,
                    codec = :codec,
                    auth_type = 0,
                    username = :username,
                    password = :password,
                    host = :host,
                    techprefix = :techprefix,
                    removedigits = :removedigits,
                    updated_at = NOW()
                WHERE id = :id
            ", [
                    'id' => $id,
                    'name' => $data['name'],
                    'codec' => $codec,
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'host' => $host,
                    'techprefix' => $techprefix,
                    'removedigits' => $data['removedigits']
                ]);

                DB::statement("
                UPDATE ps_aors
                SET contact = :contact
                WHERE id = :idname
            ", [
                    'idname' => $data['name'],
                    'contact' => 'sip:' . $data['host'] . ':5060'
                ]);

                DB::statement("
                UPDATE ps_auths
                SET username = :username,
                    password = :password
                WHERE id = :idname
            ", [
                    'idname' => $data['name'],
                    'username' => $data['username'],
                    'password' => $data['password']
                ]);

                DB::statement("
                UPDATE ps_endpoints
                SET aors = :aors,
                    auth = :auth,
                    outbound_auth = :outbound_auth,
                    outbound_proxy = 'sip:' || :host || ':5060',
                    allow = :allow
                WHERE id = :idname
            ", [
                    'idname' => $data['name'],
                    'aors' => $data['name'],
                    'auth' => $data['name'],
                    'outbound_auth' => $data['name'],
                    'host' => $data['host'],
                    'allow' => $codec
                ]);
            });

        } else {
            $username = null;
            $password = null;

            DB::transaction(function () use ($id, $data, $codec, $username, $password) {
                DB::statement("
                UPDATE voip_trunks
                SET name = :name,
                    codec = :codec,
                    auth_type = 1,
                    username = :username,
                    password = :password,
                    host = :host,
                    techprefix = :techprefix,
                    removedigits = :removedigits,
                    updated_at = NOW()
                WHERE id = :id
            ", [
                    'id' => $id,
                    'name' => $data['name'],
                    'codec' => $codec,
                    'username' => $username,
                    'password' => $password,
                    'host' => $data['host'],
                    'techprefix' => $data['techprefix'],
                    'removedigits' => $data['removedigits']
                ]);

                DB::statement("
                UPDATE ps_aors
                SET contact = :contact
                WHERE id = :idname
            ", [
                    'idname' => $data['name'],
                    'contact' => 'sip:' . $data['host'] . ':5060'
                ]);

                DB::statement("
                UPDATE ps_endpoints
                SET aors = :aors,
                    outbound_proxy = 'sip:' || :host || ':5060',
                    allow = :allow
                WHERE id = :idname
            ", [
                    'idname' => $data['name'],
                    'aors' => $data['name'],
                    'host' => $data['host'],
                    'allow' => $codec
                ]);

                DB::statement("
                UPDATE ps_endpoint_id_ips
                SET match = :match
                WHERE id = :idname
            ", [
                    'idname' => $data['name'],
                    'match' => $data['host']
                ]);
            });
        }

        return response()->json(['success' => 'Trunk atualizado com sucesso']);
    }




    public function destroy($id)
    {
        $trunks = VoipTrunk::find($id);

        if (!$trunks) {
            return back()->withErrors(['message' => 'Registro não encontrado.']);
        }

        $trunks->delete();

        return response()->json(['success' => 'Tronco VoIP excluída com sucesso'], 200);
    }

}
