<?php

namespace App\Database\Seeds;

class MahasiswaTableSeeder extends \CodeIgniter\Database\Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'nama_mahasiswa' => 'Steve Grant Rogers',
                'alamat_mahasiswa' => 'Manhattan, NY No. 405',
                'tempat_lahir' => 'Manhattan, NY',
                'tanggal_lahir' => '1980-07-04',
                'telp' => '08123456789',
                'gender' => 'L',
                'pendidikan' => 'D4/S1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Thor Odinson',
                'alamat_mahasiswa' => 'Asgard, Cave No. 179',
                'tempat_lahir' => 'Asgard',
                'tanggal_lahir' => '1975-11-01',
                'telp' => '08123456788',
                'gender' => 'L',
                'pendidikan' => 'S2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Anthony Edward Stark',
                'alamat_mahasiswa' => 'Long Island, NY No. 103',
                'tempat_lahir' => 'Long Island, NY',
                'tanggal_lahir' => '1970-05-29',
                'telp' => '08123456787',
                'gender' => 'L',
                'pendidikan' => 'S3',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Nicholas Josep Fury Jr.',
                'alamat_mahasiswa' => 'Atlanta, Georgia No. 1',
                'tempat_lahir' => 'Atlanta, Georgia',
                'tanggal_lahir' => '1963-07-04',
                'telp' => '08123456786',
                'gender' => 'L',
                'pendidikan' => 'D3',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Robert Bruce Banner',
                'alamat_mahasiswa' => 'Dayton, Ohio No. 185',
                'tempat_lahir' => 'Dayton, Ohio',
                'tanggal_lahir' => '1969-12-18',
                'telp' => '08123456785',
                'gender' => 'L',
                'pendidikan' => 'D4/S1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Natalia Alianovna Romanova',
                'alamat_mahasiswa' => 'Stalingrad No.804',
                'tempat_lahir' => 'Stalingrad',
                'tanggal_lahir' => '1984-11-22',
                'telp' => '08123456784',
                'gender' => 'P',
                'pendidikan' => 'S2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Clinton Francis Barton',
                'alamat_mahasiswa' => 'Waverly Lowa No. 902',
                'tempat_lahir' => 'Waverly, Iowa',
                'tanggal_lahir' => '1979-01-15',
                'telp' => '08123456783',
                'gender' => 'L',
                'pendidikan' => 'D3',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Wanda Maximoff',
                'alamat_mahasiswa' => 'Westview NJ No. 2800',
                'tempat_lahir' => 'Westview, NJ',
                'tanggal_lahir' => '1990-02-10',
                'telp' => '08123456782',
                'gender' => 'P',
                'pendidikan' => 'D4/S1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Vision',
                'alamat_mahasiswa' => 'Westview NJ No. 2800',
                'tempat_lahir' => 'Westview, NJ',
                'tanggal_lahir' => '2016-05-30',
                'telp' => '08123456781',
                'gender' => 'L',
                'pendidikan' => 'S2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Henry Pym',
                'alamat_mahasiswa' => 'LA No 181',
                'tempat_lahir' => 'Los Angeles, CA',
                'tanggal_lahir' => '1965-09-14',
                'telp' => '08123456780',
                'gender' => 'L',
                'pendidikan' => 'S3',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Scott Lang',
                'alamat_mahasiswa' => 'Rhinebeck, NY No. 604',
                'tempat_lahir' => 'Rhinebeck, NY',
                'tanggal_lahir' => '1985-06-02',
                'telp' => '08123456779',
                'gender' => 'L',
                'pendidikan' => 'D4/S1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_mahasiswa' => 'Gamora Zen Whoberi Ben Titan',
                'alamat_mahasiswa' => 'Zen-Whoberis No. 116',
                'tempat_lahir' => 'Zen-Whoberis',
                'tanggal_lahir' => '1992-08-18',
                'telp' => '08123456778',
                'gender' => 'P',
                'pendidikan' => 'D3',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        $this->db->table('mahasiswa')->insertBatch($data);
    }
}
