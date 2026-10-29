<?php

namespace Tests\Feature;

use App\Models\PesertaKartu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class KartuTanggalTest extends TestCase
{
    use RefreshDatabase;

    public function test_kartu_index_displays_tanggal_column(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        PesertaKartu::create([
            'no_urut' => 1,
            'tanggal' => '15/10/2026',
            'no_kartu' => '00013936084',
            'nik' => '3578190100000001',
            'nama' => 'MOCH FA',
            'instansi' => 'Dinas Per',
            'peny' => 'ASN',
            'jawaban_tinggal_copas' => 'Data yang bersangkutan telah aktif.',
            'pisat' => 'Peserta',
            'status_aktif' => 'AKTIF',
            'jns_kpst' => 'PEGAWAI',
            'terdaftar' => 'JAMKESDA',
            'tunggakan' => '0',
        ]);

        $response = $this->actingAs($user)->get(route('kartu.index'));

        $response->assertStatus(200);
        $response->assertSee('TANGGAL');
        $response->assertSee('15/10/2026');
        $response->assertSee('00013936084');
        $response->assertSee('MOCH FA');
    }

    public function test_kartu_export_downloads_excel(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('kartu.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_kartu_template_downloads_excel(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('import.kartu.format'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_import_kartu_saves_tanggal(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        // Buat file excel sementara
        $filePath = tempnam(sys_get_temp_dir(), 'test_kartu_') . '.xlsx';
        $writer = new Writer();
        $writer->openToFile($filePath);
        $writer->getCurrentSheet()->setName('Data Kartu');

        $writer->addRow(Row::fromValues([
            'No',
            'Tanggal',
            'NO. KARTU',
            'NIK',
            'NAMA',
            'INSTANSI',
            'Penyelenggara',
            'Jawaban Tinggal Copas',
            'PISAT',
            'Status Aktif',
            'Jns Kpst',
            'Terdaftar',
            'Tunggakan',
        ]));

        $writer->addRow(Row::fromValues([
            1,
            '29/10/2026',
            '00013936084',
            '3578190100000002',
            'ANANDA TEST',
            'Satuan Polisi',
            'ASN',
            'Data yang bersangkutan telah aktif',
            'Peserta',
            'AKTIF',
            'PBI (APBN)',
            'JAMKESDA',
            0,
        ]));

        $writer->close();

        $uploadedFile = new UploadedFile(
            $filePath,
            'test_kartu.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($user)->post(route('import.kartu.proses'), [
            'file' => $uploadedFile,
        ]);

        $response->assertRedirect(route('import.peserta'));
        $this->assertDatabaseHas('peserta_kartus', [
            'nik' => '3578190100000002',
            'nama' => 'ANANDA TEST',
            'tanggal' => '29/10/2026',
            'no_kartu' => '00013936084',
        ]);

        @unlink($filePath);
    }

    public function test_system_migrate_endpoint(): void
    {
        $response = $this->get('/system/migrate?key=neon-migrate-123');
        $response->assertStatus(200);
        $response->assertSee('Migrasi Database Berhasil');
        $this->assertDatabaseHas('users', [
            'email' => 'admin.pemadanan@gmail.com',
            'role' => 'admin',
        ]);
    }
}
