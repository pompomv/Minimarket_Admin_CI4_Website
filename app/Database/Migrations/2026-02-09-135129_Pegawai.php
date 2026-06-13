<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Pegawai extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pegawai' => [
                'type' => 'INT',
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
            ],
            'nip' => [
                'type' => 'VARCHAR',
                'constraint' => '10',
                'null' => FALSE,
            ],
            'nama_pegawai' => [
                'type' => 'VARCHAR',
                'constraint' => '30',
                'null' => FALSE,
            ],
            'alamat_pegawai' => [
                'type' => 'TEXT',
                'null' => FALSE,
            ],
            'telp' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
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

        $this->forge->addKey('id_pegawai', true);
        $this->forge->createTable('pegawai');
    }

    public function down()
    {
        $this->forge->dropTable('pegawai');
    }
}
