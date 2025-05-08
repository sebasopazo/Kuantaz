<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BeneficioAggregatedControllerTest extends TestCase
{
    /**
     * @covers \App\Http\Controllers\BeneficioAggregatedController
     */
    public function test_can_fetch_beneficios_grouped_by_year(): void
    {
        // Mocking external HTTP responses
        Http::fake([
            'https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02' => Http::response([
                'data' => [
                    [
                        "id_programa" => 147,
                        "monto" => 40656,
                        "fecha_recepcion" => "09/11/2023",
                        "fecha" => "2023-11-09"
                    ],
                ]
            ], 200),

            'https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf' => Http::response([
                'data' => [
                    [
                        "id_programa" => 147,
                        "tramite" => "Emprende",
                        "min" => 0,
                        "max" => 50000,
                        "ficha_id" => 922
                    ],
                ]
            ], 200),

            'https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687' => Http::response([
                'data' => [
                    [
                        "id" => 922,
                        "nombre" => "Emprende",
                        "id_programa" => 147,
                        "url" => "emprende",
                        "categoria" => "trabajo",
                        "descripcion" => "Fondos concursables para nuevos negocios"
                    ],
                ]
            ], 200),
        ]);

        $response = $this->getJson('/beneficios-procesados');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'year',
                         'num',
                         'beneficios' => [
                             '*' => [
                                 'id_programa',
                                 'monto',
                                 'fecha_recepcion',
                                 'fecha',
                                 'ano',
                                 'view',
                                 'ficha' => [
                                     'id',
                                     'nombre',
                                     'url',
                                     'categoria',
                                     'descripcion'
                                 ]
                             ]
                         ]
                     ]
                 ]);
    }
}
