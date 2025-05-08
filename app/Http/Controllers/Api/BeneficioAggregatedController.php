<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;
/**
 * @OA\Info(
 *     title="Beneficios API",
 *     description="API for retrieving aggregated beneficios",
 *     version="1.0.0",
 *     @OA\Contact(
 *         email="contact@myapi.com"
 *     )
 * )
 */
class BeneficioAggregatedController extends Controller
{
/**
  * @OA\Get(
  *     path="/beneficios-procesados",
  *     summary="Get aggregated beneficios grouped by year",
  *     tags={"Beneficios"},
  *     @OA\Response(
  *         response=200,
  *         description="Successful response",
  *         @OA\JsonContent(
  *             type="array",
  *             @OA\Items(
  *                 @OA\Property(property="year", type="string"),
  *                 @OA\Property(property="num", type="integer"),
  *                 @OA\Property(
  *                     property="beneficios-procesados",
  *                     type="array",
  *                     @OA\Items(
  *                         @OA\Property(property="id_programa", type="integer"),
  *                         @OA\Property(property="monto", type="number"),
  *                         @OA\Property(property="fecha_recepcion", type="string", format="date"),
  *                         @OA\Property(property="fecha", type="string", format="date"),
  *                         @OA\Property(property="ano", type="string"),
  *                         @OA\Property(property="view", type="boolean"),
  *                         @OA\Property(property="ficha", type="object")
  *                     )
  *                 )
  *             )
  *         )
  *     )
  * )
  */
   public function index(): JsonResponse
   {
       // Gets
       $beneficios = Http::withOptions(['verify' => false])
                         ->get('https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02')
                         ->json()['data'];

       $filtros = Http::withOptions(['verify' => false])
                      ->get('https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf')
                      ->json()['data'];

       $fichas = Http::withOptions(['verify' => false])
                     ->get('https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687')
                     ->json()['data'];

       $filtrosByPrograma = collect($filtros)->keyBy('id_programa');
       $fichasById = collect($fichas)->keyBy('id');

       // Filter beneficios based on monto ranges from filtros
       $beneficiosFiltrados = collect($beneficios)->filter(function ($beneficio) use ($filtrosByPrograma) {
           $filtro = $filtrosByPrograma[$beneficio['id_programa']] ?? null;
           if (!$filtro) return false;

           return $beneficio['monto'] >= $filtro['min'] && $beneficio['monto'] <= $filtro['max'];
       })->map(function ($beneficio) use ($filtrosByPrograma, $fichasById) {
           $filtro = $filtrosByPrograma[$beneficio['id_programa']];
           $ficha = $fichasById[$filtro['ficha_id']] ?? null;

           $año = date('Y', strtotime($beneficio['fecha']));

           return [
               'id_programa' => $beneficio['id_programa'],
               'monto' => $beneficio['monto'],
               'fecha_recepcion' => $beneficio['fecha_recepcion'],
               'fecha' => $beneficio['fecha'],
               'ano' => $año,  //  "ano" -> "año"
               'view' => true,
               'ficha' => $ficha
           ];
       });

       // Group beneficios by year
       $agrupadoPorAño = $beneficiosFiltrados
           ->groupBy('ano')  //
           ->sortKeysDesc()
           ->map(function ($beneficios, $ano) {
               return [
                   'year' => $ano,
                   'num' => $beneficios->count(),
                   'beneficios' => $beneficios->values()
               ];
           })
           ->values();

       return response()->json($agrupadoPorAño);
   }

}
