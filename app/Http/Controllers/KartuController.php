<?php

namespace App\Http\Controllers;

use App\Models\PesertaKartu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class KartuController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));
        $instansi = trim($request->input('instansi', ''));
        $statusAktif = trim($request->input('status_aktif', ''));

        $data = PesertaKartu::query()
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('nama', 'like', "%{$keyword}%")
                        ->orWhere('nik', 'like', "%{$keyword}%")
                        ->orWhere('no_kartu', 'like', "%{$keyword}%")
                        ->orWhere('tanggal', 'like', "%{$keyword}%")
                        ->orWhere('instansi', 'like', "%{$keyword}%")
                        ->orWhere('status_aktif', 'like', "%{$keyword}%")
                        ->orWhere('jns_kpst', 'like', "%{$keyword}%")
                        ->orWhere('jawaban_tinggal_copas', 'like', "%{$keyword}%");
                });
            })
            ->when($instansi !== '', function ($q) use ($instansi) {
                $q->where('instansi', $instansi);
            })
            ->when($statusAktif !== '', function ($q) use ($statusAktif) {
                $q->where('status_aktif', $statusAktif);
            })
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        $daftarInstansi = PesertaKartu::query()
            ->whereNotNull('instansi')
            ->where('instansi', '<>', '')
            ->select('instansi')
            ->distinct()
            ->orderBy('instansi')
            ->pluck('instansi');

        $daftarStatus = PesertaKartu::query()
            ->whereNotNull('status_aktif')
            ->where('status_aktif', '<>', '')
            ->select('status_aktif')
            ->distinct()
            ->orderBy('status_aktif')
            ->pluck('status_aktif');

        return view('kartu.index', compact(
            'data',
            'keyword',
            'instansi',
            'statusAktif',
            'daftarInstansi',
            'daftarStatus'
        ));
    }

    public function export(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));
        $instansi = trim($request->input('instansi', ''));
        $statusAktif = trim($request->input('status_aktif', ''));

        $path = tempnam(sys_get_temp_dir(), 'data_kartu_') . '.xlsx';

        $writer = new XlsxWriter();

        try {
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

            PesertaKartu::query()
                ->when($keyword !== '', function ($q) use ($keyword) {
                    $q->where(function ($sub) use ($keyword) {
                        $sub->where('nama', 'like', "%{$keyword}%")
                            ->orWhere('nik', 'like', "%{$keyword}%")
                            ->orWhere('no_kartu', 'like', "%{$keyword}%")
                            ->orWhere('tanggal', 'like', "%{$keyword}%")
                            ->orWhere('instansi', 'like', "%{$keyword}%")
                            ->orWhere('status_aktif', 'like', "%{$keyword}%")
                            ->orWhere('jns_kpst', 'like', "%{$keyword}%")
                            ->orWhere(
                                'jawaban_tinggal_copas',
                                'like',
                                "%{$keyword}%"
                            );
                    });
                })
                ->when($instansi !== '', function ($q) use ($instansi) {
                    $q->where('instansi', $instansi);
                })
                ->when($statusAktif !== '', function ($q) use ($statusAktif) {
                    $q->where('status_aktif', $statusAktif);
                })
                ->orderBy('id')
                ->chunk(500, function ($items) use ($writer) {
                    foreach ($items as $item) {
                        $writer->addRow(Row::fromValues([
                            $item->no_urut,
                            $item->tanggal,
                            $item->no_kartu,
                            $item->nik,
                            $item->nama,
                            $item->instansi,
                            $item->peny,
                            $item->jawaban_tinggal_copas,
                            $item->pisat,
                            $item->status_aktif,
                            $item->jns_kpst,
                            $item->terdaftar,
                            $item->tunggakan,
                        ]));
                    }
                });

            $writer->close();

            return response()
                ->download($path, 'data-kartu.xlsx')
                ->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            try {
                $writer->close();
            } catch (\Throwable $ignored) {
                // Abaikan jika writer sudah tertutup.
            }

            if (file_exists($path)) {
                @unlink($path);
            }

            report($e);

            return back()->with(
                'error',
                'Gagal mengunduh Data Kartu. Periksa log aplikasi.'
            );
        }
    }

    public function destroyAll()
    {
        $jumlah = PesertaKartu::count();

        DB::transaction(function () {
            PesertaKartu::query()->delete();
        });

        return redirect()
            ->route('kartu.index')
            ->with(
                'success',
                'Berhasil menghapus ' .
                number_format($jumlah, 0, ',', '.') .
                ' data kartu. Data Peserta tidak dihapus.'
            );
    }
}