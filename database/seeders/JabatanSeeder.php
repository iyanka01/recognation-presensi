<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultJabatans = [
            ['nama_jabatan' => 'Kepala Sekolah', 'kategori' => 'guru', 'keterangan' => 'Pimpinan Lembaga Pendidikan', 'urutan' => 1],
            ['nama_jabatan' => 'Wakil Kepala Sekolah', 'kategori' => 'guru', 'keterangan' => 'Wakil Kepala Sekolah', 'urutan' => 2],
            ['nama_jabatan' => 'Guru Mata Pelajaran', 'kategori' => 'guru', 'keterangan' => 'Guru Pengampu Mata Pelajaran', 'urutan' => 3],
            ['nama_jabatan' => 'Guru BK', 'kategori' => 'guru', 'keterangan' => 'Guru Bimbingan dan Konseling', 'urutan' => 4],
            ['nama_jabatan' => 'Wali Kelas', 'kategori' => 'guru', 'keterangan' => 'Wali Kelas Peserta Didik', 'urutan' => 5],
            ['nama_jabatan' => 'Kepala Tata Usaha (KTU)', 'kategori' => 'staff', 'keterangan' => 'Kepala Tenaga Administrasi Sekolah', 'urutan' => 6],
            ['nama_jabatan' => 'Staff Tata Usaha (TU)', 'kategori' => 'staff', 'keterangan' => 'Tenaga Administrasi Sekolah', 'urutan' => 7],
            ['nama_jabatan' => 'Staff Keuangan / Bendahara', 'kategori' => 'staff', 'keterangan' => 'Pengelola Keuangan Sekolah', 'urutan' => 8],
            ['nama_jabatan' => 'Staff Kepegawaian', 'kategori' => 'staff', 'keterangan' => 'Administrasi Kepegawaian', 'urutan' => 9],
            ['nama_jabatan' => 'Staff Kurikulum', 'kategori' => 'staff', 'keterangan' => 'Administrasi Akademik & Kurikulum', 'urutan' => 10],
            ['nama_jabatan' => 'Staff Perpustakaan / Pustakawan', 'kategori' => 'staff', 'keterangan' => 'Pengelola Perpustakaan', 'urutan' => 11],
            ['nama_jabatan' => 'Laboran / Teknisi IT', 'kategori' => 'staff', 'keterangan' => 'Pengelola Laboratorium & IT', 'urutan' => 12],
            ['nama_jabatan' => 'Petugas Keamanan (Satpam)', 'kategori' => 'staff', 'keterangan' => 'Keamanan Lingkungan Sekolah', 'urutan' => 13],
            ['nama_jabatan' => 'Petugas Kebersihan', 'kategori' => 'staff', 'keterangan' => 'Kebersihan Lingkungan Sekolah', 'urutan' => 14],
        ];

        foreach ($defaultJabatans as $item) {
            Jabatan::firstOrCreate(
                ['nama_jabatan' => $item['nama_jabatan']],
                $item
            );
        }
    }
}
