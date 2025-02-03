<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function getBackups()
    {
        $backups = Backup::paginate(10);
        return response()->json($backups);
    }

    public function upsertBackup(Request $request)
    {
        $apiKey = $request->header('X-API-KEY');

        if (!$apiKey || $apiKey !== 'B4ackupArteArena25') {
            return response()->json(['erro' => 'API Key inválida'], 401);
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'data_inicio' => 'required|date_format:Y-m-d H:i:s',
            'data_fim' => 'nullable|date_format:Y-m-d H:i:s',
            'status' => 'required|in:sucesso,falha,em_andamento',
            'tamanho' => 'nullable|integer',
        ]);

        // Busca um backup com o mesmo nome e data de início
        $backup = Backup::where('nome', $request->nome)
            ->where('data_inicio', $request->data_inicio)
            ->first();

        if ($backup) {
            // Se o backup existe, atualiza os dados
            $backup->update($request->all());
        } else {
            // Se o backup não existe, cria um novo
            $backup = Backup::create($request->all());
        }

        return response()->json($backup); // Retorna o backup criado ou atualizado
    }
}
