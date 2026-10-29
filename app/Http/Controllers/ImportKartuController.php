<?php

namespace App\Http\Controllers;

use App\Models\PesertaKartu;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ODS\Reader as OdsReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use RuntimeException;
use Throwable;

class ImportKartuController extends Controller
{
    private const BATCH = 500;
    private const MAKS_PESAN_ERROR = 50;
    private const MAKS_BARIS_JUDUL = 20;

    private const KOLOM_PERSIS = [
        'NO' => 'no_urut',
        'NOMOR' => 'no_urut',
        'TANGGAL' => 'tanggal',
        'TGL' => 'tanggal',
        'NOKARTU' => 'no_kartu',
        'NIK' => 'nik',
        'NAMA' => 'nama',
        'INSTANSI' => 'instansi',
        'PENYELENGGARA' => 'peny',
        'JAWABANTINGGALCOPAS' => 'jawaban_tinggal_copas',
        'PISAT' => 'pisat',
        'STATUSAKTIF' => 'status_aktif',
        'JNSKPST' => 'jns_kpst',
        'TERDAFTAR' => 'terdaftar',
        'TUNGGAKAN' => 'tunggakan',
    ];

    /**
     * Menampilkan halaman impor.
     */
    public function index()
    {
        return view('import-peserta');
    }

    /**
     * Mengunduh template Excel Format 2.
     */
    public function template()
    {
        $namaFile = 'format-data-kartu.xlsx';
        $path = tempnam(sys_get_temp_dir(), 'template_kartu_');

        if ($path === false) {
            return redirect()
                ->route('import.peserta')
                ->with('error', 'Gagal menyiapkan template Excel.');
        }

        $writer = null;

        try {
            $writer = new XlsxWriter();
            $writer->openToFile($path);
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

            $writer->close();
            $writer = null;

            return response()
                ->download($path, $namaFile)
                ->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            if ($writer !== null) {
                try {
                    $writer->close();
                } catch (Throwable $closeError) {
                    // Abaikan kesalahan ketika menutup writer.
                }
            }

            if (file_exists($path)) {
                @unlink($path);
            }

            report($e);

            return redirect()
                ->route('import.peserta')
                ->with('error', 'Gagal membuat template Excel Format 2.');
        }
    }

    /**
     * Mengimpor file Format 2.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'extensions:xlsx,csv,ods',
                'max:20480',
            ],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.extensions' => 'File harus berformat .xlsx, .csv, atau .ods.',
            'file.max' => 'Ukuran file maksimal 20 MB.',
        ]);

        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $file = $request->file('file');
        $ekstensi = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        $reader = $this->buatReader($ekstensi);

        $hasil = [
            'baru' => 0,
            'diperbarui' => 0,
            'gagal' => 0,
        ];

        $errors = [];
        $transaksiDimulai = false;

        try {
            $reader->open($path);

            DB::beginTransaction();
            $transaksiDimulai = true;

            $sheet = null;

            foreach ($reader->getSheetIterator() as $itemSheet) {
                $sheet = $itemSheet;
                break;
            }

            if ($sheet === null) {
                throw new RuntimeException(
                    'File tidak memiliki sheet yang dapat dibaca.'
                );
            }

            $kolom = null;
            $kolomTambahan = [];
            $batch = [];
            $nomorBaris = 0;
            $nikTerlihat = [];

            foreach ($sheet->getRowIterator() as $row) {
                $nomorBaris++;
                $cells = $this->nilaiBaris($row);

                if ($kolom === null) {
                    if ($nomorBaris > self::MAKS_BARIS_JUDUL) {
                        break;
                    }

                    [$kolomBaru, $tambahanBaru] =
                        $this->bacaJudul($cells);

                    if (in_array('nik', $kolomBaru, true)) {
                        if (!in_array('nama', $kolomBaru, true)) {
                            throw new RuntimeException(
                                'Kolom NAMA tidak ditemukan pada judul Excel.'
                            );
                        }

                        $kolom = $kolomBaru;
                        $kolomTambahan = $tambahanBaru;
                    }

                    continue;
                }

                $data = [];

                foreach ($kolom as $indeks => $namaKolom) {
                    $data[$namaKolom] = $this->bersihkan(
                        $cells[$indeks] ?? null
                    );
                }

                if (
                    ($data['nik'] ?? null) === null
                    && ($data['nama'] ?? null) === null
                ) {
                    continue;
                }

                $nikAsli = $data['nik'] ?? '';
                $nik = preg_replace(
                    '/\D/',
                    '',
                    (string) $nikAsli
                ) ?? '';

                if (strlen($nik) !== 16) {
                    $hasil['gagal']++;

                    $this->catatError(
                        $errors,
                        $nomorBaris,
                        $nikAsli,
                        $data['nama'] ?? null,
                        'NIK harus terdiri atas 16 digit angka.'
                    );

                    continue;
                }

                if (($data['nama'] ?? null) === null) {
                    $hasil['gagal']++;

                    $this->catatError(
                        $errors,
                        $nomorBaris,
                        $nik,
                        null,
                        'Nama peserta kosong.'
                    );

                    continue;
                }

                if (isset($nikTerlihat[$nik])) {
                    $hasil['gagal']++;

                    $this->catatError(
                        $errors,
                        $nomorBaris,
                        $nik,
                        $data['nama'],
                        'NIK duplikat. Sudah ditemukan pada baris ke-'
                            . $nikTerlihat[$nik] . '.'
                    );

                    continue;
                }

                $nikTerlihat[$nik] = $nomorBaris;

                $tambahan = [];

                foreach ($kolomTambahan as $indeks => $judul) {
                    $nilai = $this->bersihkan(
                        $cells[$indeks] ?? null
                    );

                    if ($nilai !== null) {
                        $tambahan[$judul] = $nilai;
                    }
                }

                $no = $data['no_urut'] ?? null;

                $batch[$nik] = [
                    'no_urut' => (
                        $no !== null && ctype_digit($no)
                    ) ? (int) $no : null,
                    'tanggal' => $data['tanggal'] ?? null,
                    'no_kartu' => $data['no_kartu'] ?? null,
                    'nik' => $nik,
                    'nama' => $data['nama'],
                    'instansi' => $data['instansi'] ?? null,
                    'peny' => $data['peny'] ?? null,
                    'jawaban_tinggal_copas' =>
                        $data['jawaban_tinggal_copas'] ?? null,
                    'pisat' => $data['pisat'] ?? null,
                    'status_aktif' => $data['status_aktif'] ?? null,
                    'jns_kpst' => $data['jns_kpst'] ?? null,
                    'terdaftar' => $data['terdaftar'] ?? null,
                    'tunggakan' => $data['tunggakan'] ?? null,
                    'data_lain' => $tambahan === []
                        ? null
                        : json_encode(
                            $tambahan,
                            JSON_UNESCAPED_UNICODE
                        ),
                ];

                if (count($batch) >= self::BATCH) {
                    $this->simpanBatch($batch, $hasil);
                    $batch = [];
                }
            }

            if ($kolom === null) {
                throw new RuntimeException(
                    'Judul kolom tidak ditemukan dalam 20 baris pertama. '
                    . 'Pastikan file memiliki kolom NIK dan NAMA.'
                );
            }

            $this->simpanBatch($batch, $hasil);

            DB::commit();
            $transaksiDimulai = false;

            $reader->close();
        } catch (Throwable $e) {
            if ($transaksiDimulai) {
                DB::rollBack();
            }

            try {
                $reader->close();
            } catch (Throwable $closeError) {
                // Abaikan kesalahan ketika menutup reader.
            }

            report($e);

            return redirect()
                ->route('import.peserta')
                ->with(
                    'error',
                    'Import Format 2 gagal. Penyebab: ' . $e->getMessage()
                );
        }

        $pesan = sprintf(
            'Import Format 2 selesai. Data baru: %s, diperbarui: %s, gagal: %s.',
            number_format($hasil['baru'], 0, ',', '.'),
            number_format($hasil['diperbarui'], 0, ',', '.'),
            number_format($hasil['gagal'], 0, ',', '.')
        );

        $redirect = redirect()
            ->route('import.peserta')
            ->with('success', $pesan)
            ->with('import_errors_total', $hasil['gagal']);

        if ($errors !== []) {
            $redirect->with('import_errors', $errors);
        }

        return $redirect;
    }

    /**
     * Membuat reader sesuai jenis file.
     */
    private function buatReader(string $ekstensi)
    {
        if ($ekstensi === 'csv') {
            return new CsvReader(
                new CsvOptions(
                    FIELD_DELIMITER: $this->deteksiDelimiter()
                )
            );
        }

        if ($ekstensi === 'ods') {
            return new OdsReader();
        }

        return new XlsxReader();
    }

    /**
     * Mendeteksi pemisah CSV.
     */
    private function deteksiDelimiter(): string
    {
        $file = request()->file('file');
        $path = $file?->getRealPath();

        if (!$path) {
            return ',';
        }

        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return ',';
        }

        $barisPertama = (string) fgets($handle);
        fclose($handle);

        return substr_count($barisPertama, ';')
            > substr_count($barisPertama, ',')
            ? ';'
            : ',';
    }

    /**
     * Membaca nilai sel.
     */
    private function nilaiBaris($row): array
    {
        $hasil = [];

        foreach ($row->cells as $indeks => $cell) {
            $hasil[$indeks] = $cell instanceof FormulaCell
                ? $cell->getComputedValue()
                : $cell->getValue();
        }

        return $hasil;
    }

    /**
     * Memetakan judul Excel ke kolom database.
     */
    private function bacaJudul(array $cells): array
    {
        $kolom = [];
        $tambahan = [];

        foreach ($cells as $indeks => $sel) {
            $judulAsli = $this->bersihkan($sel);

            if ($judulAsli === null) {
                continue;
            }

            $normal = preg_replace(
                '/[^A-Z0-9]/',
                '',
                mb_strtoupper($judulAsli)
            ) ?? '';

            $namaKolom = self::KOLOM_PERSIS[$normal] ?? null;

            if (
                $namaKolom === null
                && str_starts_with($normal, 'PENY')
            ) {
                $namaKolom = 'peny';
            }

            if (
                $namaKolom === null
                && str_starts_with($normal, 'TUNGGAKAN')
            ) {
                $namaKolom = 'tunggakan';
            }

            if ($namaKolom !== null) {
                if (!in_array($namaKolom, $kolom, true)) {
                    $kolom[$indeks] = $namaKolom;
                } else {
                    $tambahan[$indeks] = $judulAsli;
                }
            } else {
                $tambahan[$indeks] = $judulAsli;
            }
        }

        return [$kolom, $tambahan];
    }

    /**
     * Membersihkan nilai sel.
     */
    private function bersihkan(mixed $nilai): ?string
    {
        if (
            $nilai === null
            || is_array($nilai)
            || is_object($nilai)
        ) {
            return null;
        }

        if ($nilai instanceof DateTimeInterface) {
            return $nilai->format('d/m/Y');
        }

        if (is_bool($nilai)) {
            return $nilai ? 'TRUE' : 'FALSE';
        }

        if (is_float($nilai)) {
            $nilai = floor($nilai) === $nilai
                ? sprintf('%.0f', $nilai)
                : (string) $nilai;
        }

        $nilai = trim((string) $nilai);

        if ($nilai === '') {
            return null;
        }

        $nilai = preg_replace('/^\xEF\xBB\xBF/', '', $nilai) ?? $nilai;

        return $nilai === '' ? null : $nilai;
    }

    /**
     * Menyimpan data secara bertahap.
     */
    private function simpanBatch(array $batch, array &$hasil): void
    {
        if ($batch === []) {
            return;
        }

        $sekarang = now();
        $niks = array_map('strval', array_keys($batch));

        $sudahAda = PesertaKartu::whereIn('nik', $niks)->count();

        $hasil['diperbarui'] += $sudahAda;
        $hasil['baru'] += count($niks) - $sudahAda;

        $data = [];

        foreach ($batch as $item) {
            $item['created_at'] = $sekarang;
            $item['updated_at'] = $sekarang;
            $data[] = $item;
        }

        PesertaKartu::upsert(
            $data,
            ['nik'],
            [
                'no_urut',
                'tanggal',
                'no_kartu',
                'nama',
                'instansi',
                'peny',
                'jawaban_tinggal_copas',
                'pisat',
                'status_aktif',
                'jns_kpst',
                'terdaftar',
                'tunggakan',
                'data_lain',
                'updated_at',
            ]
        );
    }

    /**
     * Mencatat maksimal 50 kesalahan.
     */
    private function catatError(
        array &$errors,
        int $baris,
        mixed $nik,
        mixed $nama,
        string $pesan
    ): void {
        if (count($errors) >= self::MAKS_PESAN_ERROR) {
            return;
        }

        $errors[] = [
            'baris' => $baris,
            'nik' => $nik ?: '-',
            'nama' => $nama ?: '-',
            'error' => $pesan,
        ];
    }
}