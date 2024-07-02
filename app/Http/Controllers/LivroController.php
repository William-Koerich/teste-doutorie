<?php

namespace App\Http\Controllers;

use App\Jobs\ImportarIndicesXmlJob;
use App\Models\Indice;
use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Facades\Validator;


class LivroController extends Controller
{
    public function index(Request $request)
    {
        $query = Livro::query();

        if ($request->has('titulo')) {
            $query->where('titulo', 'like', '%' . $request->query('titulo') . '%');
        }

        if ($request->has('titulo_do_indice')) {
            $tituloDoIndice = $request->query('titulo_do_indice');
            $query->whereHas('indices', function ($query) use ($tituloDoIndice) {
                $query->where('titulo', 'like', '%' . $tituloDoIndice . '%');
            });
        }

        $livros = $query->with(['usuarioPublicador', 'indices'])->get();

        $response = $livros->map(function ($livro) {
            return [
                'titulo' => $livro->titulo,
                'Usuario_publicador' => [
                    'id' => $livro->usuarioPublicador->id,
                    'nome' => $livro->usuarioPublicador->name
                ],
                'indices' => $this->formatIndices($livro->indices)
            ];
        });

        return response()->json($response, 200);
    }

    private function formatIndices($indices)
    {
        return $indices->map(function ($indice) {
            return [
                'id' => $indice->id,
                'titulo' => $indice->titulo,
                'pagina' => $indice->pagina,
                'subindices' => $this->formatIndices($indice->subindices)
            ];
        });
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'titulo' => 'required|string|max:255',
            'indices' => 'array',
            'indices.*.titulo' => 'required_with:indices|string|max:255',
            'indices.*.pagina' => 'required_with:indices|integer',
            'indices.*.subindices' => 'array',
            'indices.*.subindices.*.titulo' => 'required_with:indices.*.subindices|string|max:255',
            'indices.*.subindices.*.pagina' => 'required_with:indices.*.subindices|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        $livro = new Livro();
        $livro->titulo = $data['titulo'];
        $livro->usuario_publicador_id = $user->id;
        $livro->save();

        if (isset($data['indices'])) {
            $this->saveIndices($livro->id, $data['indices']);
        }

        return response()->json($livro->load('indices'), 201);
    }

    private function saveIndices($livroId, $indices, $parentIndexId = null)
    {
        foreach ($indices as $indiceData) {
            $indice = new Indice();
            $indice->livro_id = $livroId;
            $indice->indice_pai_id = $parentIndexId;
            $indice->titulo = $indiceData['titulo'];
            $indice->pagina = $indiceData['pagina'];
            $indice->save();

            if (isset($indiceData['subindices']) && is_array($indiceData['subindices'])) {
                $this->saveIndices($livroId, $indiceData['subindices'], $indice->id);
            }
        }
    }

    public function importarIndicesXml(Request $request, $livroId)
    {
        $livro = Livro::findOrFail($livroId);

        $xmlContent = $request->getContent();

        // Disparar o job para importar o XML de índices
        ImportarIndicesXmlJob::dispatch($livroId, $xmlContent);

        return response()->json(['message' => 'Importação do XML de índices iniciada com sucesso.'], 200);
    }
}
