<?php

namespace App\Http\Controllers;

use App\Models\OrcamentosUniformesMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrcamentosUniformesMedidasController extends Controller
{
    /**
     * Display a listing of all medidas.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return OrcamentosUniformesMedida::all();
    }

    /**
     * Atualizar medidas em lote.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medidas' => 'required|array',
            'medidas.*.id' => 'required|exists:orcamentos_uniformes_medidas,id',
            'medidas.*.largura_camisa' => 'required|integer',
            'medidas.*.altura_camisa' => 'required|integer',
            'medidas.*.largura_calcao' => 'required|integer',
            'medidas.*.altura_calcao' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->medidas as $medidasData) {
            $medida = OrcamentosUniformesMedida::findOrFail($medidasData['id']);
            $medida->update([
                'largura_camisa' => $medidasData['largura_camisa'],
                'altura_camisa' => $medidasData['altura_camisa'],
                'largura_calcao' => $medidasData['largura_calcao'],
                'altura_calcao' => $medidasData['altura_calcao']
            ]);
        }

        return response()->json(['message' => 'Medidas atualizadas com sucesso']);
    }
} 