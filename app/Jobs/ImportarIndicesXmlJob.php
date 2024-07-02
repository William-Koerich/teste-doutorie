<?php

namespace App\Jobs;

use App\Models\Livro;
use App\Models\Indice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class ImportarIndicesXmlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $livroId;
    protected $xmlContent;

    public function __construct($livroId, $xmlContent)
    {
        $this->livroId = $livroId;
        $this->xmlContent = $xmlContent;
    }

    public function handle()
    {
        $livro = Livro::findOrFail($this->livroId);

        try {
            $xml = new SimpleXMLElement($this->xmlContent);

            foreach ($xml->indice as $indiceXml) {
                $this->processIndice($livro, $indiceXml);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao processar XML de índices: ' . $e->getMessage());
        }
    }

    private function processIndice($livro, $indiceXml, $parentIndexId = null)
    {
        $indice = new Indice();
        $indice->livro_id = $livro->id;
        $indice->indice_pai_id = $parentIndexId;
        $indice->titulo = (string)$indiceXml->titulo;
        $indice->pagina = (int)$indiceXml->pagina;
        $indice->save();

        if (isset($indiceXml->subindices) && count($indiceXml->subindices->indice) > 0) {
            foreach ($indiceXml->subindices->indice as $subindiceXml) {
                $this->processIndice($livro, $subindiceXml, $indice->id);
            }
        }
    }
}
