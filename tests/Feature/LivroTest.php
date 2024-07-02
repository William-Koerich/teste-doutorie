<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Livro;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LivroTest extends TestCase
{
    use RefreshDatabase;

    public function testListarLivros()
    {
        // Criar livros de exemplo
        Livro::factory()->count(3)->create();

        $response = $this->getJson('api/v1/livros');
        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function testCadastrarLivro()
    {
        Passport::actingAs(
            User::factory()->create()
        );

        $response = $this->postJson('api/v1/livros', [
            'titulo' => 'Novo Livro'
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['titulo' => 'Novo Livro']);
    }

    public function testImportarIndicesXml()
    {
        Passport::actingAs(
            User::factory()->create()
        );

        $livro = Livro::factory()->create();

        $xml = '<indices>
                    <indice>
                        <titulo>Capítulo 1</titulo>
                        <pagina>1</pagina>
                        <subindices>
                            <indice>
                                <titulo>Seção 1.1</titulo>
                                <pagina>2</pagina>
                            </indice>
                        </subindices>
                    </indice>
                    <indice>
                        <titulo>Capítulo 2</titulo>
                        <pagina>10</pagina>
                    </indice>
                </indices>';

        $file = UploadedFile::fake()->createWithContent('indices.xml', $xml);

        $response = $this->postJson("api/v1/livros/{$livro->id}/importar-indices-xml", [
            'xml' => $file
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Índices importados com sucesso.']);
    }

    public function testCadastrarLivroComIndices()
    {
        Passport::actingAs(
            User::factory()->create()
        );

        $data = [
            'titulo' => 'Livro Teste',
            'indices' => [
                [
                    'titulo' => 'Capítulo 1',
                    'pagina' => 1,
                    'subindices' => [
                        [
                            'titulo' => 'Seção 1.1',
                            'pagina' => 2,
                            'subindices' => []
                        ]
                    ]
                ],
                [
                    'titulo' => 'Capítulo 2',
                    'pagina' => 3,
                    'subindices' => []
                ]
            ]
        ];

        $response = $this->postJson('api/v1/livros', $data);
        $response->assertStatus(201);
        $response->assertJsonFragment(['titulo' => 'Livro Teste']);
        $response->assertJsonStructure([
            'id',
            'titulo',
            'usuario_publicador' => [
                'id',
                'nome'
            ],
            'indices' => [
                [
                    'id',
                    'titulo',
                    'pagina',
                    'subindices' => [
                        [
                            'id',
                            'titulo',
                            'pagina',
                            'subindices' => []
                        ]
                    ]
                ]
            ]
        ]);
    }
}
