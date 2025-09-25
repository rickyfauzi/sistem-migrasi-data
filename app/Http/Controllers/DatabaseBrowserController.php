<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DatabaseBrowserController extends Controller
{
    public function listSchemas()
    {
        // daftar schema/database
        $schemas = DB::connection('pgsql_source')
            ->select("SELECT schema_name FROM information_schema.schemata
                      WHERE schema_name NOT IN ('pg_catalog','information_schema')");

        return view('browser.schemas', compact('schemas'));
    }

    public function listTables($schema)
    {
        $tables = DB::connection('pgsql_source')
            ->table('information_schema.tables')
            ->where('table_schema', $schema)
            ->pluck('table_name');

        return view('browser.tables', compact('schema', 'tables'));
    }

    public function previewTable($schema, $table)
    {
        $rows = DB::connection('pgsql_source')
            ->table($schema . '.' . $table)
            ->limit(20)
            ->get();

        return view('browser.preview', compact('schema', 'table', 'rows'));
    }
}
