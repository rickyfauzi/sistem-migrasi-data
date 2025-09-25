<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;

class EtlService
{
    /**
     * Koneksi database sumber tempat data diambil.
     */
    private $sourceConnection = 'pgsql_source';

    /**
     * Koneksi database tujuan tempat data akan disimpan.
     * Diubah sesuai permintaan untuk menargetkan database SIMRS.
     */
    private $targetConnection = 'mysql_simrs'; // <-- PERUBAHAN 1

    private $tableMap;
    private $columnMap;
    private $typeMap;

    public function __construct()
    {
        // Muat konfigurasi mapping saat service diinisialisasi
        $this->tableMap = Config::get('datamap.table_mapping', []);
        $this->columnMap = Config::get('datamap.column_mapping', []);
        $this->typeMap = Config::get('datamap.type_mapping', []);
    }

    /**
     * Menjalankan proses ETL penuh untuk sebuah tabel.
     *
     * @param string $sourceTableName
     * @return array Status dan informasi proses
     * @throws Exception
     */
    public function run(string $sourceTableName): array
    {
        try {
            if (!Schema::connection($this->sourceConnection)->hasTable($sourceTableName)) {
                throw new Exception("Tabel '{$sourceTableName}' tidak ditemukan di database PostgreSQL sumber.");
            }

            $destinationTableName = $this->tableMap[$sourceTableName] ?? $sourceTableName;
            $sourceColumns = $this->getPostgresColumns($sourceTableName);
            if (empty($sourceColumns)) {
                throw new Exception("Tidak dapat menemukan skema untuk tabel '{$sourceTableName}'.");
            }
            $totalRows = DB::connection($this->sourceConnection)->table($sourceTableName)->count();

            Log::info("Memulai proses ETL: {$sourceTableName} -> {$destinationTableName} ({$totalRows} baris) pada koneksi '{$this->targetConnection}'");

            // =================================================================
            // === PERUBAHAN LOGIKA UTAMA: CREATE IF NOT EXISTS, ELSE TRUNCATE ===
            // =================================================================

            // LANGKAH 1: Persiapan Skema (DDL) - Di luar Transaksi
            $wasTableNewlyCreated = false; // Flag untuk error handling

            if ($this->tableExists($destinationTableName, $this->targetConnection)) {
                // Jika tabel sudah ada, kosongkan (truncate) untuk diisi ulang.
                // TRUNCATE lebih cepat dari DELETE dan mereset auto-increment.
                Log::info("Tabel '{$destinationTableName}' sudah ada di MySQL. Mengosongkan tabel (TRUNCATE).");
                DB::connection($this->targetConnection)->table($destinationTableName)->truncate();
            } else {
                // Jika tabel belum ada, buat tabel baru.
                Log::info("Tabel '{$destinationTableName}' tidak ditemukan di MySQL. Membuat tabel baru.");
                $createQuery = $this->generateMysqlCreateQuery($sourceColumns, $sourceTableName, $destinationTableName);
                DB::connection($this->targetConnection)->statement($createQuery);
                Log::info("Tabel '{$destinationTableName}' berhasil dibuat di MySQL.");
                $wasTableNewlyCreated = true; // Tandai bahwa tabel baru saja dibuat
            }

            // LANGKAH 2: Operasi Data (DML) - Di dalam Transaksi
            $migratedRows = 0;
            try {
                // Proses transfer data tetap sama
                DB::connection($this->targetConnection)->transaction(function () use ($sourceTableName, $destinationTableName, &$migratedRows) {
                    $migratedRows = $this->transferData($sourceTableName, $destinationTableName);
                });
            } catch (Exception $e) {
                // Penanganan error yang lebih baik:
                // Hanya hapus tabel jika kita BARU saja membuatnya.
                // Jika tabel sudah ada sebelumnya, biarkan dalam keadaan kosong (ter-truncate).
                if ($wasTableNewlyCreated) {
                    Schema::connection($this->targetConnection)->dropIfExists($destinationTableName);
                    Log::error("Transfer data gagal, tabel '{$destinationTableName}' yang baru dibuat telah dihapus kembali.");
                } else {
                    Log::error("Transfer data gagal untuk tabel '{$destinationTableName}' yang sudah ada. Tabel telah dikosongkan tapi gagal diisi ulang.");
                }
                throw $e; // Lempar kembali error asli
            }

            Log::info("Proses ETL selesai. Berhasil memigrasi {$migratedRows} baris.");

            // Pesan sukses yang lebih informatif
            $message = "Berhasil memigrasi {$migratedRows} baris dari '{$sourceTableName}' ke '{$destinationTableName}'.";
            if ($totalRows == 0) {
                $message = "Tabel '{$sourceTableName}' berhasil diproses, namun tidak ada data untuk dimigrasi (0 baris).";
            }

            return [
                'success' => true,
                'source_table' => $sourceTableName,
                'destination_table' => $destinationTableName,
                'total_rows' => $totalRows,
                'migrated_rows' => $migratedRows,
                'message' => $message
            ];
        } catch (Exception $e) {
            Log::error("Proses ETL gagal untuk tabel {$sourceTableName}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cek apakah tabel ada di database
     */
    private function tableExists(string $tableName, string $connection): bool
    {
        try {
            return Schema::connection($connection)->hasTable($tableName);
        } catch (Exception $e) {
            // Jika koneksi gagal, anggap saja tabel tidak ada
            Log::warning("Could not check if table '{$tableName}' exists on connection '{$connection}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil detail kolom dari tabel PostgreSQL dengan informasi lengkap
     */
    private function getPostgresColumns(string $tableName): array
    {
        return DB::connection($this->sourceConnection)
            ->select("
                SELECT 
                    c.column_name, c.data_type, c.character_maximum_length,
                    c.numeric_precision, c.numeric_scale, c.is_nullable,
                    c.column_default, c.ordinal_position,
                    CASE WHEN tc.constraint_type = 'PRIMARY KEY' THEN 'YES' ELSE 'NO' END as is_primary_key,
                    CASE WHEN c.column_default LIKE 'nextval%' THEN 'YES' ELSE 'NO' END as is_auto_increment
                FROM information_schema.columns c
                LEFT JOIN information_schema.key_column_usage kcu 
                    ON c.table_name = kcu.table_name AND c.column_name = kcu.column_name
                LEFT JOIN information_schema.table_constraints tc 
                    ON kcu.constraint_name = tc.constraint_name AND tc.constraint_type = 'PRIMARY KEY'
                WHERE c.table_schema = 'public' AND c.table_name = ?
                ORDER BY c.ordinal_position
            ", [$tableName]);
    }

    /**
     * Membuat sintaks CREATE TABLE untuk MySQL dengan handling yang lebih baik
     */
    private function generateMysqlCreateQuery(array $columns, string $sourceTable, string $destTable): string
    {
        $queryParts = [];
        $primaryKeys = [];

        foreach ($columns as $column) {
            $destColName = $this->getDestinationColumnName($sourceTable, $column->column_name);
            $destColType = $this->mapDataType($column);
            $part = "`{$destColName}` {$destColType}";

            if (strtoupper($column->is_nullable) === 'NO') {
                $part .= " NOT NULL";
            } else {
                $part .= " NULL";
            }

            if ($column->is_auto_increment === 'YES') {
                $part .= " AUTO_INCREMENT";
                $primaryKeys[] = $destColName;
            } elseif ($column->column_default && !str_contains($column->column_default, 'nextval')) {
                $defaultValue = $this->formatDefaultValue($column->column_default, $column->data_type);
                if ($defaultValue !== null) {
                    $part .= " DEFAULT {$defaultValue}";
                }
            }

            if ($column->is_primary_key === 'YES' && $column->is_auto_increment === 'NO') {
                $primaryKeys[] = $destColName;
            }

            $queryParts[] = $part;
        }

        if (!empty($primaryKeys)) {
            $uniquePrimaryKeys = array_unique($primaryKeys);
            $queryParts[] = "PRIMARY KEY (`" . implode('`, `', $uniquePrimaryKeys) . "`)";
        }

        $createQuery = "CREATE TABLE `{$destTable}` (\n  " .
            implode(",\n  ", $queryParts) .
            "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        Log::info("Generated CREATE TABLE query for {$destTable}:\n{$createQuery}");
        return $createQuery;
    }

    /**
     * Get destination column name berdasarkan mapping
     */
    private function getDestinationColumnName(string $sourceTable, string $sourceColumn): string
    {
        return $this->columnMap[$sourceTable][$sourceColumn]
            ?? $this->columnMap['global'][$sourceColumn]
            ?? $sourceColumn;
    }

    /**
     * Map tipe data dengan handling yang lebih komprehensif
     */
    private function mapDataType(object $column): string
    {
        $sourceType = strtolower($column->data_type);

        if ($sourceType === 'numeric' && $column->numeric_precision) {
            $scale = $column->numeric_scale ?? 0;
            return "DECIMAL({$column->numeric_precision}, {$scale})";
        }
        if (in_array($sourceType, ['character varying', 'varchar']) && $column->character_maximum_length) {
            return "VARCHAR({$column->character_maximum_length})";
        }
        if ($sourceType === 'character' && $column->character_maximum_length) {
            return "CHAR({$column->character_maximum_length})";
        }

        foreach ($this->typeMap as $pgType => $mysqlType) {
            if (str_contains($sourceType, $pgType)) {
                return $mysqlType;
            }
        }

        return match ($sourceType) {
            'integer' => 'INT',
            'bigint' => 'BIGINT',
            'smallint' => 'SMALLINT',
            'boolean' => 'TINYINT(1)',
            'date' => 'DATE',
            'time' => 'TIME',
            'timestamp', 'timestamp without time zone' => 'DATETIME', // Menggunakan DATETIME untuk kompatibilitas lebih luas
            'text' => 'LONGTEXT',
            default => 'TEXT'
        };
    }

    /**
     * Format default value untuk MySQL
     */
    private function formatDefaultValue(?string $defaultValue, string $dataType): ?string
    {
        if ($defaultValue === null) return null;

        if (str_contains($defaultValue, '::')) {
            $defaultValue = trim(explode('::', $defaultValue)[0]);
        }
        if (str_contains($defaultValue, '(') || str_contains($defaultValue, 'nextval')) {
            return null;
        }

        if (in_array(strtolower($dataType), ['boolean', 'bool'])) {
            return match (strtolower(trim($defaultValue))) {
                'true', 't' => '1',
                'false', 'f' => '0',
                default => null
            };
        }
        if (in_array($dataType, ['character varying', 'varchar', 'text', 'character', 'char'])) {
            return "'" . str_replace("'", "''", trim($defaultValue, "'")) . "'";
        }
        if (is_numeric($defaultValue)) {
            return $defaultValue;
        }
        return null;
    }

    /**
     * Transfer semua data dari PostgreSQL ke MySQL dalam bentuk chunk.
     */
    private function transferData(string $sourceTable, string $destTable): int
    {
        $chunkSize = 500; // Ukuran untuk insert ke MySQL
        $totalMigrated = 0;
        $insertData = [];

        try {
            // Gunakan cursor() untuk mengambil data dari sumber.
            // Ini menjaga koneksi tetap aktif dan lebih efisien memori.
            $sourceRows = DB::connection($this->sourceConnection)->table($sourceTable)->cursor();

            foreach ($sourceRows as $row) {
                $newRow = [];
                foreach ((array)$row as $sourceKey => $value) {
                    $destKey = $this->getDestinationColumnName($sourceTable, $sourceKey);
                    $newRow[$destKey] = $this->convertValue($value, $sourceKey, $destKey);
                }
                $insertData[] = $newRow;

                // Ketika data yang dikumpulkan mencapai ukuran chunk, insert ke target
                if (count($insertData) >= $chunkSize) {
                    DB::connection($this->targetConnection)->table($destTable)->insert($insertData);
                    $totalMigrated += count($insertData);
                    $insertData = []; // Kosongkan array untuk chunk berikutnya

                    // Opsional: log progres
                    if ($totalMigrated % (10 * $chunkSize) === 0) {
                        Log::info("...telah memigrasi {$totalMigrated} baris...");
                    }
                }
            }

            // Insert sisa data yang belum mencapai ukuran chunk
            if (!empty($insertData)) {
                DB::connection($this->targetConnection)->table($destTable)->insert($insertData);
                $totalMigrated += count($insertData);
            }
        } catch (Exception $e) {
            // Tangkap dan lempar kembali error dengan konteks yang lebih jelas
            Log::error("Error during data transfer using cursor: " . $e->getMessage());
            throw new Exception("Gagal mentransfer data: " . $e->getMessage(), 0, $e);
        }

        return $totalMigrated;
    }

    /**
     * Convert nilai untuk kompatibilitas MySQL
     */
    private function convertValue($value, string $sourceColumnName, string $destinationColumnName): mixed
    {
        if ($value === null) return null;
        if ($destinationColumnName === 'deleted_at' && ($value === true || strtolower((string)$value) === 't')) return now();
        if (is_bool($value)) return $value ? 1 : 0;
        if (in_array(strtolower((string)$value), ['t', 'true'])) return 1;
        if (in_array(strtolower((string)$value), ['f', 'false'])) return 0;
        return $value;
    }

    /**
     * Get migration status
     */
    public function getStatus(string $tableName): array
    {
        $destinationTable = $this->tableMap[$tableName] ?? $tableName;
        $sourceExists = $this->tableExists($tableName, $this->sourceConnection);
        $targetExists = $this->tableExists($destinationTable, $this->targetConnection);
        $sourceCount = $sourceExists ? DB::connection($this->sourceConnection)->table($tableName)->count() : 0;
        $targetCount = $targetExists ? DB::connection($this->targetConnection)->table($destinationTable)->count() : 0;

        return [
            'source_table' => $tableName,
            'destination_table' => $destinationTable,
            'source_exists' => $sourceExists,
            'target_exists' => $targetExists,
            'source_count' => $sourceCount,
            'target_count' => $targetCount,
            'is_synced' => $targetExists && $sourceCount === $targetCount
        ];
    }
}
