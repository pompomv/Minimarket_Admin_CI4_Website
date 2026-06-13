<?php

namespace App\Database\Seeds;

class PegawaiTableSeeder extends \CodeIgniter\Database\Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'nip' => '2021010101',
                'nama_pegawai' => 'Steve Grant Rogers',
                'alamat_pegawai' => 'Manhattan, NY No. 405',
                'telp' => '08123456789',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010102',
                'nama_pegawai' => 'Thor Odinson',
                'alamat_pegawai' => 'Asgard, Cave No. 179',
                'telp' => '08123456788',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010103',
                'nama_pegawai' => 'Anthony Edward Stark',
                'alamat_pegawai' => 'Long Island, NY No. 103',
                'telp' => '08123456787',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010104',
                'nama_pegawai' => 'Nicholas Josep Fury Jr.',
                'alamat_pegawai' => 'Atlanta, Georgia No. 1',
                'telp' => '08123456786',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010105',
                'nama_pegawai' => 'Robert Bruce Banner',
                'alamat_pegawai' => 'Dayton, Ohio No. 185',
                'telp' => '08123456785',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010106',
                'nama_pegawai' => 'Natalia Alianovna Romanova',
                'alamat_pegawai' => 'Stalingrad No.804',
                'telp' => '08123456784',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010107',
                'nama_pegawai' => 'Clinton Francis Barton',
                'alamat_pegawai' => 'Waverly Lowa No. 902',
                'telp' => '08123456783',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010108',
                'nama_pegawai' => 'Wanda Maximoff',
                'alamat_pegawai' => 'Westview NJ No. 2800',
                'telp' => '08123456782',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010109',
                'nama_pegawai' => 'Vision',
                'alamat_pegawai' => 'Westview NJ No. 2800',
                'telp' => '08123456781',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010110',
                'nama_pegawai' => 'Henry Pym',
                'alamat_pegawai' => 'LA No 181',
                'telp' => '08123456780',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010111',
                'nama_pegawai' => 'Scott Lang',
                'alamat_pegawai' => 'Rhinebeck, NY No. 604',
                'telp' => '08123456779',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nip' => '2021010112',
                'nama_pegawai' => 'Gamora Zen Whoberi Ben Titan',
                'alamat_pegawai' => 'Zen-Whoberis No. 116',
                'telp' => '08123456778',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        $this->db->table('pegawai')->insertBatch($data);
    }
}
