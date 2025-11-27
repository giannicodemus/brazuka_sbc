<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutboundRoutes\StoreRequest;
use App\Http\Requests\OutboundRoutes\UpdateRequest;
use App\Models\OutboundRoute;
use App\Models\RandomClidList;
use App\Models\VoipAccount;
use App\Models\VoipTrunk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
class OutboundRoutesController extends Controller
{
    public function index(Request $request): Response
    {
        $outbounds = OutboundRoute::orderBy('id', 'asc')->get();
        $accounts = VoipAccount::orderBy('id', 'asc')->get();
        $trunks = VoipTrunk::orderBy('id', 'asc')->get();
        $clids = RandomClidList::orderBy('id', 'asc')->get();

        return Inertia::render('OutboundRoutes/Index', [
            'outbounds' => $outbounds,
            'accounts' => $accounts,
            'trunks' => $trunks,
            'clids' => $clids,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        try {
            OutboundRoute::create([
                'voip_account_id' => $data['voip_account_id'],
                'voip_trunk_id' => $data['voip_trunk_id'],
                'random_clid_list_id' => $data['random_clid_list_id'],
                'recording' => $data['recording'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => 'Registro criado com sucesso!',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao criar Rota de Saída.', [
                'message' => $e->getMessage(),
                'exception' => $e,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Erro ao criar o registro: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {

        $outbounds = OutboundRoute::where('id', $id)->first();

        return response()->json([
            'outbounds' => $outbounds,
        ]);
    }

    public function update(UpdateRequest $request, $id)
    {
        $data = $request->validated();

        try {
            $route = OutboundRoute::findOrFail($id);

            $route->update([
                'voip_account_id' => $data['voip_account_id'],
                'voip_trunk_id' => $data['voip_trunk_id'],
                'random_clid_list_id' => $data['random_clid_list_id'],
                'recording' => $data['recording'],
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => 'Registro atualizado com sucesso!',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Registro não encontrado.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar Rota de Saída.', [
                'message' => $e->getMessage(),
                'exception' => $e,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Erro ao atualizar o registro: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $outbounds = OutboundRoute::find($id);

        if (!$outbounds) {
            return back()->withErrors(['message' => 'Registro não encontrado.']);
        }

        $outbounds->delete();

        return response()->json(['success' => 'Rotas de Saída excluída com sucesso'], 200);
    }


}
