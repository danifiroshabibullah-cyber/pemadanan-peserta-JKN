<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\PesertaPeriode;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ODS\Reader as OdsReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use RuntimeException;
use Throwable;

class ImportPesertaController extends Controller
{
    private const UKURAN_BATCH = 500;

    private const KOLOM = [
        'no_urut' => ['NO', 'NO URUT', 'NOMOR'],
        'sk_kemensos' => ['SK KEMENSOS', 'SK', 'NO SK', 'SK KEMENSOS NO'],
        'nik' => ['NIK'],
        'nama' => ['NAMA', 'NAMA PESERTA'],
        'alamat' => ['ALAMAT'],
        'kecamatan' => ['KEC NAME', 'KECAMATAN', 'KEC'],
        'kelurahan' => ['KEL NAME', 'KELURAHAN', 'KEL', 'DESA'],
        'rw' => ['NO RW', 'RW'],
        'rt' => ['NO RT', 'RT'],
        'keterangan' => ['KETERANGAN', 'KET'],
        'faskes' => ['NAMA FASKES', 'FASKES'],
        'hasil' => ['HASIL'],
    ];

    public function index()
    {
        return view('import-peserta');
    }

    public function template()
    {
        $path = tempnam(sys_get_temp_dir(), 'template_peserta_') . '.xlsx';

        $bulanIni = Carbon::now()->startOfMonth();
        $bulanDepan = $bulanIni->copy()->addMonth();

        $writer = new XlsxWriter();
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('Data Peserta');

        $writer->addRow(Row::fromValues([
            'NO', 'SK KEMENSOS', 'NIK', 'NAMA', 'ALAMAT', 'KEC_NAME',
            'KEL_NAME', 'NO_RW', 'NO_RT', 'KETERANGAN', 'NAMA FASKES',
            $bulanIni->format('M-y'), 'SEKMEN',
            $bulanDepan->format('M-y'), 'SEKMEN',
            'hasil',
        ]));

        $writer->addRow(Row::fromValues([
            '1', 'SK-001/2026', '3578000000000001', 'CONTOH NAMA',
            'JL. CONTOH NO. 1', 'CONTOH KECAMATAN',
            'CONTOH KELURAHAN', '001', '002', '', 'KLINIK CONTOH',
            'AKTIF', 'PBI', '', '', '',
        ]));

        $writer->close();

        return response()
            ->download($path, 'format-import-peserta.xlsx')
            ->deleteFileAfterSend(true);
    }

    /**
     * Mengimpor data peserta dari Excel, CSV, atau ODS.
     */
    public function import(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'file' => ['required', 'file', 'extensions:xlsx,csv,ods', 'max:51200'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.uploaded' => 'File gagal diunggah. Periksa batas unggahan server.',
            'file.extensions' => 'Format file harus .xlsx, .csv, atau .ods.',
            'file.max' => 'Ukuran file maksimal 50 MB.',
        ]);

        $file = $request->file('file');
        $ekstensi = strtolower($file->getClientOriginalExtension());
        $reader = $this->buatReader($ekstensi, $file->getRealPath());

        try {
            $reader->open($file->getRealPath());
            $hasil = DB::transaction(fn () => $this->proses($reader));
            $reader->close();
        } catch (Throwable $e) {
            try {
                $reader->close();
            } catch (Throwable $ignored) {
                // Abaikan kegagalan saat menutup file.
            }

            report($e);

            return redirect()
                ->route('import.peserta')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }

        $pesan = sprintf(
            'Import selesai. %s data baru, %s data diperbarui',
            number_format($hasil['baru'], 0, ',', '.'),
            number_format($hasil['diperbarui'], 0, ',', '.')
        );

        if ($hasil['dilewati'] > 0) {
            $pesan .= sprintf(
                ', %s baris dilewati',
                number_format($hasil['dilewati'], 0, ',', '.')
            );
        }

        $pesan .= '.';

        if (($hasil['tanpa_nik'] ?? 0) > 0) {
            $pesan .= sprintf(
                ' Termasuk %s peserta tanpa NIK (cari lewat nama).',
                number_format($hasil['tanpa_nik'], 0, ',', '.')
            );
        }

        if (!empty($hasil['periode'])) {
            $pesan .= ' Bulan terbaca: ' . implode(', ', array_map(
                fn ($p) => PesertaPeriode::labelPeriode($p),
                $hasil['periode']
            )) . '.';
        }

        $redirect = redirect()->route('import.peserta')->with('success', $pesan);

        if (!empty($hasil['catatan'])) {
            $redirect->with('import_errors', $hasil['catatan']);
        }

        return $redirect;
    }

    /**
     * Mengekspor Data Peserta beserta status dan segmen setiap periode.
     * Tidak mengambil data dari tabel Data Kartu.
     */
    public function exportDatabase()
    {
        @set_time_limit(0);

        $path = tempnam(sys_get_temp_dir(), 'export_peserta_') . '.xlsx';
        $writer = new XlsxWriter();

        try {
            $writer->openToFile($path);
            $writer->getCurrentSheet()->setName('Data Peserta');

            $daftarPeriode = PesertaPeriode::query()
                ->select('periode')
                ->distinct()
                ->orderBy('periode')
                ->pluck('periode')
                ->all();

            $header = [
                'NO', 'SK KEMENSOS', 'NIK', 'NAMA', 'ALAMAT',
                'KEC_NAME', 'KEL_NAME', 'NO_RW', 'NO_RT',
                'KETERANGAN', 'NAMA FASKES',
            ];

            foreach ($daftarPeriode as $periode) {
                $tanggal = Carbon::createFromFormat('!Y-m', $periode);
                $header[] = $tanggal->format('M-y');
                $header[] = 'SEKMEN';
            }

            $header[] = 'hasil';
            $writer->addRow(Row::fromValues($header));

            Peserta::query()
                ->orderBy('id')
                ->chunk(self::UKURAN_BATCH, function ($peserta) use (
                    $writer,
                    $daftarPeriode
                ) {
                    $ids = $peserta->pluck('id')->all();

                    $periodePeserta = PesertaPeriode::query()
                        ->whereIn('peserta_id', $ids)
                        ->get()
                        ->groupBy('peserta_id');

                    foreach ($peserta as $item) {
                        $baris = [
                            $item->no_urut,
                            $item->sk_kemensos,
                            $item->nik,
                            $item->nama,
                            $item->alamat,
                            $item->kecamatan,
                            $item->kelurahan,
                            $item->rw,
                            $item->rt,
                            $item->keterangan,
                            $item->faskes,
                        ];

                        $dataPeriode = $periodePeserta
                            ->get($item->id, collect())
                            ->keyBy('periode');

                        foreach ($daftarPeriode as $periode) {
                            $data = $dataPeriode->get($periode);
                            $baris[] = $data?->status;
                            $baris[] = $data?->segmen;
                        }

                        $baris[] = $item->hasil;

                        $writer->addRow(Row::fromValues($baris));
                    }
                });

            $writer->close();

            return response()
                ->download($path, 'data-peserta.xlsx')
                ->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            try {
                $writer->close();
            } catch (Throwable $ignored) {
                // Abaikan jika writer sudah tertutup.
            }

            if (file_exists($path)) {
                @unlink($path);
            }

            report($e);

            return back()->with(
                'error',
                'Gagal mengekspor Data Peserta. Periksa log aplikasi.'
            );
        }
    }

    private function buatReader(string $ekstensi, string $path): ReaderInterface
    {
        if ($ekstensi === 'csv') {
            $baris = '';
            $handle = @fopen($path, 'rb');

            if ($handle) {
                $baris = (string) fgets($handle);
                fclose($handle);
            }

            $delimiter = substr_count($baris, ';') > substr_count($baris, ',')
                ? ';'
                : ',';

            return new CsvReader(new CsvOptions(FIELD_DELIMITER: $delimiter));
        }

        if ($ekstensi === 'ods') {
            return new OdsReader();
        }

        return new XlsxReader();
    }

    private function proses(ReaderInterface $reader): array
    {
        $total = [
            'baru' => 0,
            'diperbarui' => 0,
            'dilewati' => 0,
            'tanpa_nik' => 0,
            'periode' => [],
            'catatan' => [],
        ];

        $sheetDiproses = false;

        foreach ($reader->getSheetIterator() as $sheet) {
            $peta = null;
            $baris = 0;
            $batch = [];
            $sudahDilihat = [];
            $kembarTanpaNik = [];

            foreach ($sheet->getRowIterator() as $row) {
                $baris++;
                $sel = $this->nilaiBaris($row);

                if ($peta === null) {
                    $kandidat = $this->bacaHeader($sel);

                    if ($kandidat !== null) {
                        $peta = $kandidat;
                    } elseif ($baris >= 20) {
                        break;
                    }

                    continue;
                }

                if ($this->barisKosong($sel)) {
                    continue;
                }

                $item = $this->bacaBaris($sel, $peta);

                if ($item['nama'] === '') {
                    $this->catatLewati($total, $baris, $item['nik'], 'Nama kosong.');
                    continue;
                }

                if ($item['nik'] === '') {
                    $dasar = mb_strtoupper(implode('|', [
                        $item['nama'], $item['sk_kemensos'], $item['alamat'],
                        $item['rw'], $item['rt'], $item['kelurahan'],
                    ]));

                    $kode = 'TANPA-NIK-' . substr(md5($dasar), 0, 12);
                    $kembarTanpaNik[$kode] = ($kembarTanpaNik[$kode] ?? 0) + 1;

                    $item['nik'] = $kode . (
                        $kembarTanpaNik[$kode] > 1
                            ? '-' . $kembarTanpaNik[$kode]
                            : ''
                    );

                    $total['tanpa_nik']++;
                }

                if (isset($sudahDilihat[$item['nik']])) {
                    $this->catatLewati(
                        $total,
                        $baris,
                        $item['nik'],
                        'NIK ganda, sudah ada di baris ke-' . $sudahDilihat[$item['nik']] . '.'
                    );
                    continue;
                }

                $sudahDilihat[$item['nik']] = $baris;
                $batch[$item['nik']] = $item;

                if (count($batch) >= self::UKURAN_BATCH) {
                    $this->simpanBatch($batch, $peta, $total);
                    $batch = [];
                }
            }

            if ($peta !== null) {
                $sheetDiproses = true;

                if ($batch) {
                    $this->simpanBatch($batch, $peta, $total);
                }

                break;
            }
        }

        if (!$sheetDiproses) {
            throw new RuntimeException(
                'Baris judul kolom tidak ditemukan. Pastikan file memiliki kolom NIK dan NAMA.'
            );
        }

        $total['periode'] = array_values($total['periode']);
        sort($total['periode']);

        return $total;
    }

    private function bacaHeader(array $sel): ?array
    {
        $kolom = [];
        $bulan = [];
        $normal = [];

        foreach ($sel as $i => $nilai) {
            $normal[$i] = $this->normalisasiHeader($nilai);
        }

        foreach (self::KOLOM as $field => $alias) {
            foreach ($normal as $i => $teks) {
                if (in_array($teks, $alias, true) && !isset($kolom[$field])) {
                    $kolom[$field] = $i;
                }
            }
        }

        if (!isset($kolom['nik'], $kolom['nama'])) {
            return null;
        }

        foreach ($sel as $i => $nilai) {
            $periode = $this->bacaPeriode($nilai);

            if ($periode === null) {
                continue;
            }

            $segmen = null;

            if (isset($normal[$i + 1]) && in_array(
                $normal[$i + 1],
                ['SEKMEN', 'SEGMEN'],
                true
            )) {
                $segmen = $i + 1;
            }

            $bulan[$i] = ['periode' => $periode, 'segmen' => $segmen];
        }

        return ['kolom' => $kolom, 'bulan' => $bulan];
    }

    private function bacaBaris(array $sel, array $peta): array
    {
        $ambil = function (string $field) use ($sel, $peta): string {
            if (!isset($peta['kolom'][$field])) {
                return '';
            }

            return $this->teks($sel[$peta['kolom'][$field]] ?? null);
        };

        $nik = preg_replace('/[\s\'`]+/u', '', $ambil('nik')) ?? '';
        $periode = [];

        foreach ($peta['bulan'] as $indeks => $info) {
            $status = $this->teks($sel[$indeks] ?? null);
            $segmen = $info['segmen'] !== null
                ? $this->teks($sel[$info['segmen']] ?? null)
                : '';

            if ($status === '' && $segmen === '') {
                continue;
            }

            $periode[$info['periode']] = [
                'status' => $status !== '' ? $status : null,
                'segmen' => $segmen !== '' ? $segmen : null,
            ];
        }

        $noUrut = $ambil('no_urut');

        return [
            'nik' => $nik,
            'nama' => $ambil('nama'),
            'no_urut' => is_numeric($noUrut) ? (int) $noUrut : null,
            'sk_kemensos' => $this->kosongJadiNull($ambil('sk_kemensos')),
            'alamat' => $this->kosongJadiNull($ambil('alamat')),
            'rt' => $this->kosongJadiNull($ambil('rt')),
            'rw' => $this->kosongJadiNull($ambil('rw')),
            'kelurahan' => $this->kosongJadiNull($ambil('kelurahan')),
            'kecamatan' => $this->kosongJadiNull($ambil('kecamatan')),
            'faskes' => $this->kosongJadiNull($ambil('faskes')),
            'keterangan' => $this->kosongJadiNull($ambil('keterangan')),
            'hasil' => $this->kosongJadiNull($ambil('hasil')),
            'periode' => $periode,
        ];
    }

    private function simpanBatch(array $batch, array $peta, array &$total): void
    {
        $sekarang = now();
        $niks = array_map('strval', array_keys($batch));

        $sudahAda = Peserta::whereIn('nik', $niks)->count();
        $total['baru'] += count($niks) - $sudahAda;
        $total['diperbarui'] += $sudahAda;

        $adaKolomHasil = isset($peta['kolom']['hasil']);
        $dataPeserta = [];

        foreach ($batch as $item) {
            $dataPeserta[] = [
                'nik' => $item['nik'],
                'nama' => $item['nama'],
                'no_urut' => $item['no_urut'],
                'sk_kemensos' => $item['sk_kemensos'],
                'alamat' => $item['alamat'],
                'rt' => $item['rt'],
                'rw' => $item['rw'],
                'kelurahan' => $item['kelurahan'],
                'kecamatan' => $item['kecamatan'],
                'faskes' => $item['faskes'],
                'keterangan' => $item['keterangan'],
                'hasil' => $item['hasil'],
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }

        $kolomUpdate = [
            'nama', 'no_urut', 'sk_kemensos', 'alamat', 'rt', 'rw',
            'kelurahan', 'kecamatan', 'faskes', 'keterangan', 'updated_at',
        ];

        if ($adaKolomHasil) {
            $kolomUpdate[] = 'hasil';
        }

        Peserta::upsert($dataPeserta, ['nik'], $kolomUpdate);

        $idPeserta = Peserta::whereIn('nik', $niks)->pluck('id', 'nik');
        $dataPeriode = [];

        foreach ($batch as $item) {
            $id = $idPeserta[$item['nik']] ?? null;

            if ($id === null) {
                continue;
            }

            foreach ($item['periode'] as $periode => $isi) {
                $dataPeriode[] = [
                    'peserta_id' => $id,
                    'periode' => $periode,
                    'status' => $isi['status'],
                    'segmen' => $isi['segmen'],
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];

                $total['periode'][$periode] = $periode;
            }
        }

        foreach (array_chunk($dataPeriode, 1000) as $potongan) {
            PesertaPeriode::upsert(
                $potongan,
                ['peserta_id', 'periode'],
                ['status', 'segmen', 'updated_at']
            );
        }
    }

    private function catatLewati(
        array &$total,
        int $baris,
        string $acuan,
        string $alasan
    ): void {
        $total['dilewati']++;

        if (count($total['catatan']) < 100) {
            $total['catatan'][] = "Baris {$baris}"
                . ($acuan !== '' ? " ({$acuan})" : '')
                . ": {$alasan}";
        }
    }

    private function nilaiBaris(Row $row): array
    {
        $nilai = [];

        foreach ($row->cells as $indeks => $sel) {
            $nilai[$indeks] = $sel instanceof FormulaCell
                ? $sel->getComputedValue()
                : $sel->getValue();
        }

        return $nilai;
    }

    private function barisKosong(array $sel): bool
    {
        foreach ($sel as $nilai) {
            if ($this->teks($nilai) !== '') {
                return false;
            }
        }

        return true;
    }

    private function kosongJadiNull(string $nilai): ?string
    {
        return $nilai === '' ? null : $nilai;
    }

    private function teks(mixed $nilai): string
    {
        if ($nilai === null) {
            return '';
        }

        if ($nilai instanceof DateTimeInterface) {
            return $nilai->format('Y-m-d');
        }

        if (is_bool($nilai)) {
            return $nilai ? 'TRUE' : 'FALSE';
        }

        if (is_float($nilai)) {
            return floor($nilai) === $nilai
                ? number_format($nilai, 0, '', '')
                : rtrim(rtrim(sprintf('%.10F', $nilai), '0'), '.');
        }

        if (is_array($nilai) || is_object($nilai)) {
            return '';
        }

        $teks = trim((string) $nilai);

        return preg_replace('/^\xEF\xBB\xBF/', '', $teks) ?? $teks;
    }

    private function normalisasiHeader(mixed $nilai): string
    {
        if ($nilai instanceof DateTimeInterface) {
            return '';
        }

        $teks = mb_strtoupper($this->teks($nilai));
        $teks = preg_replace('/[\s\_\.]+/u', ' ', $teks) ?? $teks;

        return trim($teks);
    }

    private function bacaPeriode(mixed $nilai): ?string
    {
        if ($nilai instanceof DateTimeInterface) {
            return $nilai->format('Y-m');
        }

        $teks = mb_strtoupper(trim($this->teks($nilai)));

        if ($teks === '') {
            return null;
        }

        if (preg_match('/^(\d{4})[-\/](\d{1,2})$/', $teks, $m)) {
            $bulan = (int) $m[2];

            return ($bulan >= 1 && $bulan <= 12)
                ? sprintf('%s-%02d', $m[1], $bulan)
                : null;
        }

        $daftar = [
            'JAN' => 1, 'JANUARI' => 1, 'JANUARY' => 1,
            'FEB' => 2, 'FEBRUARI' => 2, 'FEBRUARY' => 2,
            'MAR' => 3, 'MARET' => 3, 'MARCH' => 3,
            'APR' => 4, 'APRIL' => 4,
            'MEI' => 5, 'MAY' => 5,
            'JUN' => 6, 'JUNI' => 6, 'JUNE' => 6,
            'JUL' => 7, 'JULI' => 7, 'JULY' => 7,
            'AGU' => 8, 'AGT' => 8, 'AGUSTUS' => 8,
            'AUG' => 8, 'AUGUST' => 8,
            'SEP' => 9, 'SEPT' => 9, 'SEPTEMBER' => 9,
            'OKT' => 10, 'OCT' => 10, 'OKTOBER' => 10, 'OCTOBER' => 10,
            'NOV' => 11, 'NOVEMBER' => 11, 'NOP' => 11,
            'DES' => 12, 'DEC' => 12, 'DESEMBER' => 12, 'DECEMBER' => 12,
        ];

        if (!preg_match('/^([A-Z]+)[\s\-\/\.]+(\d{2}|\d{4})$/u', $teks, $m)) {
            return null;
        }

        if (!isset($daftar[$m[1]])) {
            return null;
        }

        $tahun = strlen($m[2]) === 2 ? '20' . $m[2] : $m[2];

        return sprintf('%s-%02d', $tahun, $daftar[$m[1]]);
    }
}