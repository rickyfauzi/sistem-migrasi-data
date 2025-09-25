<?php
// config/datamap.php - File konfigurasi mapping yang diperlukan

return [
    'table_mapping' => [
        // Mapping nama tabel: 'source_table' => 'destination_table'
        'patient' => 'patients', // PostgreSQL 'patient' menjadi MySQL 'patients'
        // Tambahkan mapping tabel lain jika diperlukan
    ],

    'column_mapping' => [
        'global' => [
            // Mapping kolom global yang berlaku untuk semua tabel
            'pid' => 'id',
            'name_real' => 'name',
            'place_of_birth' => 'place',
            'identification_cards' => 'id_card',
            'employment' => 'job',
            'marital_status' => 'married_status',
            'latest_education' => 'last_education',
            'blood_type' => 'blood_group',
            'alergy' => 'allergy',
            'citizenship' => 'citizenship',
            'mobile_number' => 'mobile_phone_number',
            'address_village' => 'ward',
            'address_subdistrict' => 'subdistrict',
            'address_regency' => 'regency',
            'address_province' => 'province',
            'meid' => 'ethnic_id',
            'bahasa' => 'language',
            'is_del' => 'deleted_at',
            'create_time' => 'created_at',
            'modify_time' => 'updated_at',
        ],
        'patient' => [
            // Mapping khusus untuk tabel patient
            'pid' => 'id',
            'old_rm' => 'medical_record_number',
            'name_real' => 'name',
            'place_of_birth' => 'place',
            'identification_cards' => 'id_card',
            'employment' => 'job',
            'marital_status' => 'married_status',
            'latest_education' => 'last_education',
            'blood_type' => 'blood_group',
            'alergy' => 'allergy',
            'mobile_number' => 'mobile_phone_number',
            'address_village' => 'ward',
            'address_subdistrict' => 'subdistrict',
            'address_regency' => 'regency',
            'address_province' => 'province',
            'nopeg' => 'nomor_kepegawaian',
            'nama_pegawai' => 'nama_pegawai',
            'hub_pegawai' => 'hubungan_pegawai',
            'bagian' => 'bagian_pegawai',
            'subgroup' => 'grup_perusahaan',
            'is_del' => 'deleted_at',
            'create_time' => 'created_at',
            'modify_time' => 'updated_at',
        ],
    ],

    'type_mapping' => [
        // Mapping tipe data PostgreSQL ke MySQL
        'integer' => 'INT',
        'bigint' => 'BIGINT',
        'smallint' => 'SMALLINT',
        'serial' => 'INT UNSIGNED',
        'bigserial' => 'BIGINT UNSIGNED',
        'varchar' => 'VARCHAR',
        'character varying' => 'VARCHAR',
        'text' => 'TEXT',
        'char' => 'CHAR',
        'character' => 'CHAR',
        'boolean' => 'BOOLEAN',
        'date' => 'DATE',
        'time' => 'TIME',
        'timestamp' => 'TIMESTAMP',
        'timestamptz' => 'TIMESTAMP',
        'numeric' => 'DECIMAL',
        'decimal' => 'DECIMAL',
        'real' => 'FLOAT',
        'double precision' => 'DOUBLE',
        'bytea' => 'LONGBLOB',
        'json' => 'JSON',
        'jsonb' => 'JSON',
        'uuid' => 'CHAR(36)',
    ],
];
