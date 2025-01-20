<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CalendarEventController extends Controller
{
    public function upsertCalendar(Request $request)
    {
        // Validação dos dados
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:evento,feriado_nacional,feriado_estadual,feriado_municipal,ponto_facultativo_externo,ponto_facultativo_interno',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        // Insere ou atualiza os dados
        CalendarEvent::upsert(
            [$data], // Dados a serem inseridos ou atualizados
            ['id'], // Colunas para identificar duplicatas
            ['description', 'category', 'end_at', 'is_active'] // Colunas que podem ser atualizadas
        );

        return response()->json(['message' => 'Evento salvo com sucesso!']);
    }

    public function getAllCalendarEventsUnfiltered()
    {
        $events = CalendarEvent::all();

        return response()->json(['events' => $events]);
    }



    public function getAllCalendarEvents(Request $request)
    {
        $events = CalendarEvent::where('category', 'evento')
            ->get();

        return response()->json(['events' => $events]);
    }

    public function getHolidaysBetweenCalendarEvents(Request $request)
    {
        $startAt = $request->query('datainicio');
        $endAt = $request->query('datafim');

        // Gera uma chave única para o cache com base nos parâmetros
        $cacheKey = "holidays:{$startAt}-{$endAt}";

        // Tenta obter os dados do cache
        $holidays = Cache::rememberForever($cacheKey, function () use ($startAt, $endAt) {
            return CalendarEvent::where('category', 'feriado_nacional')
                ->whereBetween('start_at', [$startAt, $endAt])
                ->count();
        });

        return response()->json(['dias_feriados' => $holidays]);
    }

    
    public function getHolidaysByMonthAndYear(Request $request)
    {
        $month = $request->query('mes');
        $year = $request->query('ano');

        $cacheKey = "holidays:{$year}-{$month}";

        $holidays = Cache::rememberForever($cacheKey, function () use ($month, $year) {
            $startAt = date('Y-m-d', strtotime("{$year}-{$month}-01"));
            $endAt = date('Y-m-d', strtotime("{$year}-{$month}-01 +5 month"));

            return CalendarEvent::where('category', 'feriado_nacional')
                ->whereBetween('start_at', [$startAt, $endAt])
                ->get();
        });

        return response()->json(['dias_feriados' => $holidays]);
    }

}
