<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\EtlService; // <-- Import Service
use Exception;

class DataPostgresController extends Controller
{
    public function index()
    {
        $tables = DB::connection('pgsql_source')
            ->select("SELECT table_name FROM information_schema.tables WHERE table_schema='public' ORDER BY table_name;");

        return view('postgres.index', ['tables' => $tables]);
    }

    public function show($table)
    {
        $rows = DB::connection('pgsql_source')->table($table)->limit(50)->get();
        return view('postgres.show', [
            'table' => $table,
            'rows'  => $rows
        ]);
    }

    // Ubah metode export menjadi seperti ini
    public function export(Request $request, EtlService $etlService)
    {
        $sourceTableName = $request->input('table_name');
        if (!$sourceTableName) {
            return redirect()->route('postgres.index')->with('error', 'Nama tabel tidak valid.');
        }

        try {
            // Panggil service untuk melakukan semua pekerjaan
            $destinationTableName = $etlService->run($sourceTableName);

            return redirect()->route('postgres.show', $sourceTableName)
                ->with('success', "Tabel '{$sourceTableName}' berhasil diekspor ke tabel '{$destinationTableName}' di MySQL.");
        } catch (Exception $e) {
            // Tangkap error dari service
            return redirect()->route('postgres.show', $sourceTableName)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
