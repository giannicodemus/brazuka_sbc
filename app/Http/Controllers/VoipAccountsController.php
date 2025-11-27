<?php

namespace App\Http\Controllers;

use App\Models\VoipAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VoipAccountsController extends Controller
{
    public function index(Request $request): Response
    {
        $accounts = VoipAccount::orderBy('id', 'asc')->get();
        $id = VoipAccount::max('id');
        $lastId = $id + 1;
        $password = Str::random(12);

        return Inertia::render('VoipAccounts/Index', [
            'accounts' => $accounts,
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
        ]);

        $id = $data['id'] ?? (DB::table('voip_accounts')->max('id') + 1);

        if ($data['auth_type'] == '0') {
            $username = $data['username'];
            $password = $data['password'];

            DB::transaction(function () use ($id, $data, $username, $password) {
                $codec = $data['codec'] ?? 'g729,alaw,ulaw';
                DB::statement("
                INSERT INTO voip_accounts (id, name, codec, auth_type, username, password, created_at, updated_at)
                VALUES (:id, :name, :codec, 0, :username, :password, NOW(), NOW())
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $id,
                    'name' => $data['name'],
                    'codec' => $codec,
                    'username' => $username,
                    'password' => $password,
                ]);

                DB::statement("
                INSERT INTO ps_aors (id, max_contacts)
                VALUES (:id, 1)
                ON CONFLICT (id) DO NOTHING
            ", ['id' => $username]);

                DB::statement("
                INSERT INTO ps_auths (id, auth_type, username, password)
                VALUES (:id, 'userpass', :username, :password)
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $username,
                    'username' => $username,
                    'password' => $password,
                ]);

                DB::statement("
                INSERT INTO ps_endpoints (id, transport, aors, auth, context, disallow, allow)
                VALUES (:id, 'transport-udp', :aors, :auth, 'brazukasbc', 'all', 'g729,alaw,ulaw')
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $username,
                    'aors' => $username,
                    'auth' => $username,
                ]);
            });

        } else {
            $host = $data['host'];
            $techprefix = $data['techprefix'];

            DB::transaction(function () use ($id, $data, $host, $techprefix) {
                $codec = $data['codec'] ?? 'g729,alaw,ulaw';
                DB::statement("
                INSERT INTO voip_accounts (id, name, codec, auth_type, host, techprefix, created_at, updated_at)
                VALUES (:id, :name, :codec, 1, :host, :techprefix, NOW(), NOW())
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $id,
                    'name' => $data['name'],
                    'codec' => $codec,
                    'host' => $host,
                    'techprefix' => $techprefix,
                ]);

                DB::statement("
                INSERT INTO ps_aors (id, max_contacts)
                VALUES (:id, 1)
                ON CONFLICT (id) DO NOTHING
            ", ['id' => $id]);

                DB::statement("
                INSERT INTO ps_endpoints (id, transport, aors, context, disallow, allow)
                VALUES (:id, 'transport-udp', :aors, 'brazukasbc', 'all', 'g729,alaw,ulaw')
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $id,
                    'aors' => $id,
                ]);

                DB::statement("
                INSERT INTO ps_endpoint_id_ips (id, endpoint, match)
                VALUES (:id, :endpoint, :match)
                ON CONFLICT (id) DO NOTHING
            ", [
                    'id' => $id,
                    'endpoint' => $id,
                    'match' => $host,
                ]);
            });
        }

        return response()->json(['success' => 'Conta VoIP criada com sucesso']);
    }

    public function edit($id)
    {

        $accounts = VoipAccount::where('id', $id)->first();

        return response()->json([
            'accounts' => $accounts,
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
        ]);

        $codec = $data['codec'] ?? 'g729,alaw,ulaw';

        DB::transaction(function () use ($id, $data, $codec) {
            if ($data['auth_type'] == '0') {
                $username = $data['username'];
                $password = $data['password'];

                DB::statement("
                UPDATE voip_accounts
                SET name = :name, codec = :codec, auth_type = 0, username = :username, password = :password, host = NULL, techprefix = NULL, updated_at = NOW()
                WHERE id = :id
            ", [
                    'id' => $id,
                    'name' => $data['name'],
                    'codec' => $codec,
                    'username' => $username,
                    'password' => $password,
                ]);

                DB::statement("
                UPDATE ps_aors SET max_contacts = 1 WHERE id = :username
            ", ['username' => $username]);

                DB::statement("
                UPDATE ps_auths SET auth_type = 'userpass', username = :username, password = :password WHERE id = :username
            ", [
                    'username' => $username,
                    'password' => $password,
                ]);

                DB::statement("
                UPDATE ps_endpoints SET transport = 'transport-udp', aors = :aors, auth = :auth, context = 'brazukasbc', disallow = 'all', allow = 'g729,alaw,ulaw'
                WHERE id = :username
            ", [
                    'aors' => $username,
                    'auth' => $username,
                    'username' => $username,
                ]);

            } else {
                $host = $data['host'];
                $techprefix = $data['techprefix'];

                DB::statement("
                UPDATE voip_accounts
                SET name = :name, codec = :codec, auth_type = 1, username = NULL, password = NULL, host = :host, techprefix = :techprefix, updated_at = NOW()
                WHERE id = :id
            ", [
                    'id' => $id,
                    'name' => $data['name'],
                    'codec' => $codec,
                    'host' => $host,
                    'techprefix' => $techprefix,
                ]);

                DB::statement("
                UPDATE ps_aors SET max_contacts = 1 WHERE id = :id
            ", ['id' => $id]);

                DB::statement("
                UPDATE ps_endpoints SET transport = 'transport-udp', aors = :aors, context = 'brazukasbc', disallow = 'all', allow = 'g729,alaw,ulaw'
                WHERE id = :id
            ", [
                    'id' => $id,
                    'aors' => $id,
                ]);

                DB::statement("
                UPDATE ps_endpoint_id_ips SET endpoint = :endpoint, match = :match WHERE id = :id
            ", [
                    'id' => $id,
                    'endpoint' => $id,
                    'match' => $host,
                ]);
            }
        });

        return response()->json(['success' => 'Conta VoIP atualizada com sucesso']);
    }

    public function destroy($id)
    {
        $account = VoipAccount::find($id);

        if (!$account) {
            return back()->withErrors(['message' => 'Registro não encontrado.']);
        }

        $account->delete();

        return response()->json(['success' => 'Conta VoIP excluída com sucesso'], 200);
    }


}
