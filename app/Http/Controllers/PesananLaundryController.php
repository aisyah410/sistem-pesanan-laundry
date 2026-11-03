<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PDO;

class PesananLaundryController extends Controller
{
    // Menentukan status berdasarkan berat laundry
    private function statusBerat($berat)
    {
        if ($berat >= 10) {
            return 'Besar';
        }

        return 'Reguler';
    }

    // READ: Menampilkan data, pencarian, dan filter
    public function index(Request $request)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $keyword = trim($request->query('q', ''));
            $layananTerpilih = $request->query('jenis_layanan_id', '');

            $sql = "SELECT p.*, j.nama_layanan
                    FROM pesanan_laundry p
                    INNER JOIN jenis_layanan j
                    ON p.jenis_layanan_id = j.id
                    WHERE 1=1";

            $params = [];

            // Pencarian berdasarkan kode, nama, dan nomor telepon
            if ($keyword !== '') {
                $sql .= " AND (
                    p.kode_pesanan LIKE ?
                    OR p.nama_pelanggan LIKE ?
                    OR p.nomor_telepon LIKE ?
                )";

                $cari = "%{$keyword}%";

                $params[] = $cari;
                $params[] = $cari;
                $params[] = $cari;
            }

            // Filter berdasarkan jenis layanan
            if (
                $layananTerpilih !== '' &&
                ctype_digit((string) $layananTerpilih)
            ) {
                $sql .= " AND p.jenis_layanan_id = ?";
                $params[] = (int) $layananTerpilih;
            }

            $sql .= " ORDER BY p.id DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $pesanan = $stmt->fetchAll(PDO::FETCH_OBJ);

            // Menentukan status berat setiap pesanan
            foreach ($pesanan as $item) {
                $item->status_berat = $this->statusBerat(
                    $item->berat_kg
                );
            }

            // Mengambil daftar jenis layanan
            $stmt = $pdo->prepare(
                "SELECT id, nama_layanan
                 FROM jenis_layanan
                 ORDER BY nama_layanan"
            );

            $stmt->execute();

            $layananList = $stmt->fetchAll(PDO::FETCH_OBJ);

            return view('pesanan-laundry.index', compact(
                'pesanan',
                'layananList',
                'keyword',
                'layananTerpilih'
            ));

        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()
                ->with('gagal', 'Data gagal ditampilkan.');
        }
    }

    // CREATE: Menambahkan pesanan baru
    public function store(Request $request)
    {
        $request->merge([
            'kode_pesanan' => strip_tags(
                trim($request->input('kode_pesanan', ''))
            ),
            'nama_pelanggan' => strip_tags(
                trim($request->input('nama_pelanggan', ''))
            ),
            'nomor_telepon' => strip_tags(
                trim($request->input('nomor_telepon', ''))
            ),
        ]);

        // Validasi input
        $validated = $request->validate([
            'kode_pesanan' => [
                'required',
                'alpha_num',
                'size:10',
                'unique:pesanan_laundry,kode_pesanan'
            ],
            'nama_pelanggan' => [
                'required',
                'string',
                'min:3',
                'max:100'
            ],
            'nomor_telepon' => [
                'required',
                'string',
                'min:10',
                'max:20',
                'unique:pesanan_laundry,nomor_telepon'
            ],
            'berat_kg' => [
                'required',
                'integer',
                'min:1',
                'max:20'
            ],
            'jenis_layanan_id' => [
                'required',
                'integer',
                'exists:jenis_layanan,id'
            ],
        ]);

        try {
            $pdo = DB::connection()->getPdo();

            $sql = "INSERT INTO pesanan_laundry
                    (
                        kode_pesanan,
                        nama_pelanggan,
                        nomor_telepon,
                        berat_kg,
                        jenis_layanan_id,
                        created_at,
                        updated_at
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $waktu = date('Y-m-d H:i:s');

            $stmt->execute([
                $validated['kode_pesanan'],
                $validated['nama_pelanggan'],
                $validated['nomor_telepon'],
                $validated['berat_kg'],
                $validated['jenis_layanan_id'],
                $waktu,
                $waktu
            ]);

            return redirect()
                ->route('pesanan-laundry.index')
                ->with('berhasil', 'Pesanan berhasil ditambahkan.');

        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->with('gagal', 'Pesanan gagal disimpan.');
        }
    }

    // EDIT: Mengambil data pesanan untuk diedit
    public function edit($id)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare(
                "SELECT * FROM pesanan_laundry WHERE id = ?"
            );

            $stmt->execute([$id]);

            $pesanan = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$pesanan) {
                return redirect()
                    ->route('pesanan-laundry.index')
                    ->with('gagal', 'Pesanan tidak ditemukan.');
            }

            $stmt = $pdo->prepare(
                "SELECT id, nama_layanan
                 FROM jenis_layanan
                 ORDER BY nama_layanan"
            );

            $stmt->execute();

            $layananList = $stmt->fetchAll(PDO::FETCH_OBJ);

            return view('pesanan-laundry.edit', compact(
                'pesanan',
                'layananList'
            ));

        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('pesanan-laundry.index')
                ->with('gagal', 'Form edit gagal dibuka.');
        }
    }

    // UPDATE: Memperbarui data pesanan
    public function update(Request $request, $id)
    {
        $request->merge([
            'kode_pesanan' => strip_tags(
                trim($request->input('kode_pesanan', ''))
            ),
            'nama_pelanggan' => strip_tags(
                trim($request->input('nama_pelanggan', ''))
            ),
            'nomor_telepon' => strip_tags(
                trim($request->input('nomor_telepon', ''))
            ),
        ]);

        // Validasi data dan pengecekan duplikat
        $validated = $request->validate([
            'kode_pesanan' => [
                'required',
                'alpha_num',
                'size:10',
                Rule::unique(
                    'pesanan_laundry',
                    'kode_pesanan'
                )->ignore($id)
            ],
            'nama_pelanggan' => [
                'required',
                'string',
                'min:3',
                'max:100'
            ],
            'nomor_telepon' => [
                'required',
                'string',
                'min:10',
                'max:20',
                Rule::unique(
                    'pesanan_laundry',
                    'nomor_telepon'
                )->ignore($id)
            ],
            'berat_kg' => [
                'required',
                'integer',
                'min:1',
                'max:20'
            ],
            'jenis_layanan_id' => [
                'required',
                'integer',
                'exists:jenis_layanan,id'
            ],
        ]);

        try {
            $pdo = DB::connection()->getPdo();

            $sql = "UPDATE pesanan_laundry SET
                    kode_pesanan = ?,
                    nama_pelanggan = ?,
                    nomor_telepon = ?,
                    berat_kg = ?,
                    jenis_layanan_id = ?,
                    updated_at = ?
                    WHERE id = ?";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $validated['kode_pesanan'],
                $validated['nama_pelanggan'],
                $validated['nomor_telepon'],
                $validated['berat_kg'],
                $validated['jenis_layanan_id'],
                date('Y-m-d H:i:s'),
                $id
            ]);

            return redirect()
                ->route('pesanan-laundry.index')
                ->with('berhasil', 'Pesanan berhasil diperbarui.');

        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->with('gagal', 'Pesanan gagal diperbarui.');
        }
    }

    // DELETE: Menghapus pesanan
    public function destroy($id)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare(
                "DELETE FROM pesanan_laundry WHERE id = ?"
            );

            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                return redirect()
                    ->route('pesanan-laundry.index')
                    ->with('gagal', 'Pesanan tidak ditemukan.');
            }

            return redirect()
                ->route('pesanan-laundry.index')
                ->with('berhasil', 'Pesanan berhasil dihapus.');

        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('pesanan-laundry.index')
                ->with('gagal', 'Pesanan gagal dihapus.');
        }
    }
}