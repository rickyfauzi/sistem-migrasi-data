<?php
// config/datamap.php - Versi yang Disesuaikan dengan Skema Anda

return [
    'table_mapping' => [
        'patient' => 'patients', // Ini sudah benar
    ],

    'column_mapping' => [
        'global' => [
            // Hanya simpan aturan yang benar-benar global di sini
            'create_time' => 'created_at',
            'modify_time' => 'updated_at',
            'is_del'      => 'deleted_at', // Transformasi boolean ke timestamp akan dihandle service
        ],

        // Mapping yang SANGAT SPESIFIK untuk tabel 'patient' -> 'patients'
        'patient' => [
            'pid'                  => 'id',
            'old_rm'               => 'medical_record_number',
            // 'family_id' tidak ada di source, akan NULL di target
            // 'penjamin_id' tidak ada di source, akan NULL di target
            'name_real'            => 'name',
            'place_of_birth'       => 'place',
            'date_of_birth'        => 'date_of_birth',
            'nickname'             => 'nickname',
            'title'                => 'title',
            'gender'               => 'gender', // Perlu konversi nilai ('L'/'P' -> 'Laki-Laki'/'Perempuan'?)
            'religion'             => 'religion',
            'blood_type'           => 'blood_group',
            'alergy'               => 'allergy',
            'marital_status'       => 'married_status',
            'bahasa'               => 'language',
            'citizenship'          => 'citizenship',
            'identification_cards' => 'id_card',
            'address'              => 'address',
            'address_village'      => 'ward',
            'address_subdistrict'  => 'subdistrict',
            'address_regency'      => 'regency',
            'address_province'     => 'province',
            'mobile_number'        => 'mobile_phone_number',
            'email'                => 'email',
            'latest_education'     => 'last_education',
            'meid'                 => 'ethnic', // Diasumsikan 'meid' adalah ethnic
            'employment'           => 'job',
            // 'nama_penjamin' tidak ada di target
            // 'nomor_penjamin' tidak ada di target
            'nama_pegawai'         => 'nama_pegawai',
            // 'nama_perusahaan_pegawai' tidak ada di source
            'hub_pegawai'          => 'hubungan_pegawai',
            'nopeg'                => 'nomor_kepegawaian',
            'bagian'               => 'bagian_pegawai',
            'subgroup'             => 'grup_perusahaan',
            // 'is_del' sudah ada di global, akan menjadi 'deleted_at'
            // 'create_time' sudah ada di global, akan menjadi 'created_at'
            // 'modify_time' sudah ada di global, akan menjadi 'updated_at'
        ],
    ],

    'type_mapping' => [
        // Mapping tipe data ini sudah cukup baik, kita biarkan saja
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
        'boolean' => 'BOOLEAN', // service akan mengubah ini menjadi TINYINT(1)
        'date' => 'DATE',
        'time' => 'TIME',
        'timestamp' => 'TIMESTAMP',
        'numeric' => 'DECIMAL',
        'double precision' => 'DOUBLE',
        'bytea' => 'LONGBLOB',
        'jsonb' => 'JSON',
        'uuid' => 'CHAR(36)',
    ],
];
