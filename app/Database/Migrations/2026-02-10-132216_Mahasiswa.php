<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Mahasiswa extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_mahasiswa' => [
                'type' => 'INT',
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
            ],
            'nama_mahasiswa' => [
                'type' => 'VARCHAR',
                'constraint' => '30',
                'null' => FALSE,
            ],
            'alamat_mahasiswa' => [
                'type' => 'TEXT',
                'null' => FALSE,
            ],
            'tempat_lahir' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => FALSE,
            ],
            'tanggal_lahir' => [
                'type' => 'DATE',
                'null' => FALSE,
            ],
            'telp' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => FALSE,
            ],
            'gender' => [
                'type' => 'ENUM',
                'constraint' => ['L', 'P'],
                'null' => FALSE,
            ],
            'pendidikan' => [
                'type' => 'ENUM',
                'constraint' => ['D3', 'D4/S1', 'S2', 'S3'],
                'null' => FALSE,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ]
        ]);

        $this->forge->addKey('id_mahasiswa', true);
        $this->forge->createTable('mahasiswa');
    }

    public function down()
    {
        $this->forge->dropTable('mahasiswa');
    }
}
