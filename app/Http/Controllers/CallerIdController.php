<?php

namespace App\Http\Controllers;

use App\Http\Requests\CallerIdRequest\StoreRequest;
use App\Http\Requests\CallerIdRequest\UpdateRequest;
use App\Models\Clid;
use App\Models\RandomClidList;
use App\Models\VoipTrunk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;


class CallerIdController extends Controller
{
    public function index(Request $request): Response
    {
        $randomclids = RandomClidList::orderBy('id', 'asc')->get();
        $clids = Clid::orderBy('id', 'asc')->get();


        return Inertia::render('Clid/Index', [
            'clids' => $clids,
            'randomclids' => $randomclids,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $randomclid = RandomClidList::create([
                'name' => $data['name'],
            ]);

            foreach ($data['clids'] as $clid) {
                Clid::create([
                    'random_clid_list_id' => $randomclid->id,
                    'order' => $clid['order'],
                    'number' => $clid['number'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => 'Registro criado com sucesso!',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao criar lista de CLIDs.', [
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
        $randomclids = RandomClidList::with('clids')
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'randomclids' => [
                'id' => $randomclids->id,
                'name' => $randomclids->name,
            ],
            'clids' => $randomclids->clids->map(fn($clids) => [
                'id' => $clids->id,
                'order' => $clids->order,
                'number' => $clids->number,
            ]),
        ]);


    }

    public function update(UpdateRequest $request, $id)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $randomclid = RandomClidList::findOrFail($id);
            $randomclid->update([
                'name' => $data['name']
            ]);

            $existingIds = $randomclid->clids()->pluck('id')->toArray();
            $sentIds = collect($data['clids'])->pluck('id')->filter()->toArray();

            $toDelete = array_diff($existingIds, $sentIds);
            if (!empty($toDelete)) {
                Clid::whereIn('id', $toDelete)->delete();
            }

            foreach ($data['clids'] as $clid) {
                if (!empty($clid['id'])) {
                    Clid::where('id', $clid['id'])->update([
                        'order' => $clid['order'],
                        'number' => $clid['number'],
                    ]);
                } else {
                    Clid::create([
                        'random_clid_list_id' => $randomclid->id,
                        'order' => $clid['order'],
                        'number' => $clid['number'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Registro atualizado com sucesso!',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar lista de CLIDs.', [
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
        $randomclid = RandomClidList::with('clids')->find($id);

        if (!$randomclid) {
            return response()->json(['error' => 'Registro não encontrado.'], 404);
        }

        try {
            DB::beginTransaction();
            $randomclid->clids()->delete();
            $randomclid->delete();
            DB::commit();
            return response()->json(['success' => 'Lista e CLIDs excluídos com sucesso.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao deletar lista de CLIDs.', [
                'message' => $e->getMessage(),
                'exception' => $e,
                'id' => $id,
            ]);
            return response()->json(['error' => 'Erro ao excluir o registro.'], 500);
        }
    }

}
