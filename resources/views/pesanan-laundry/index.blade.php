<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pesanan Laundry</title>
</head>
<body>

    <h1>SISTEM PESANAN LAUNDRY</h1>
    <p>Aplikasi Pengelolaan Pesanan Laundry</p>

    <hr>

    <h2>Form Tambah Pesanan</h2>

    @if (session('berhasil'))
        <p>{{ session('berhasil') }}</p>
    @endif

    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif

    @if ($errors->any())
        <h3>Terjadi Kesalahan:</h3>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('pesanan-laundry.store') }}" method="POST">
        @csrf

        <p>
            <label>Kode Pesanan:</label><br>
            <input type="text" name="kode_pesanan"
                value="{{ old('kode_pesanan') }}"
                maxlength="10" required>
        </p>

        <p>
            <label>Nama Pelanggan:</label><br>
            <input type="text" name="nama_pelanggan"
                value="{{ old('nama_pelanggan') }}"
                minlength="3" maxlength="100" required>
        </p>

        <p>
            <label>Nomor Telepon:</label><br>
            <input type="text" name="nomor_telepon"
                value="{{ old('nomor_telepon') }}"
                minlength="10" maxlength="20" required>
        </p>

        <p>
            <label>Berat Laundry (kg):</label><br>
            <input type="number" name="berat_kg"
                value="{{ old('berat_kg') }}"
                min="1" max="20" required>
        </p>

        <p>
            <label>Jenis Layanan:</label><br>
            <select name="jenis_layanan_id" required>
                <option value="">-- Pilih Layanan --</option>

                @foreach ($layananList as $layanan)
                    <option value="{{ $layanan->id }}"
                        {{ old('jenis_layanan_id') == $layanan->id ? 'selected' : '' }}>
                        {{ $layanan->nama_layanan }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Pesanan</button>
    </form>

    <hr>

    <h2>Pencarian dan Filter Pesanan</h2>

    <form action="{{ route('pesanan-laundry.index') }}" method="GET">
        <p>
            <label>Kata Kunci:</label><br>
            <input type="text" name="q"
                value="{{ $keyword }}"
                placeholder="Cari kode, nama, atau nomor telepon">
        </p>

        <p>
            <label>Jenis Layanan:</label><br>
            <select name="jenis_layanan_id">
                <option value="">-- Semua Layanan --</option>

                @foreach ($layananList as $layanan)
                    <option value="{{ $layanan->id }}"
                        {{ (string) $layananTerpilih === (string) $layanan->id ? 'selected' : '' }}>
                        {{ $layanan->nama_layanan }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Cari / Filter</button>
        <a href="{{ route('pesanan-laundry.index') }}">Reset</a>
    </form>

    <hr>

    <h2>Daftar Pesanan Laundry</h2>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Pesanan</th>
                <th>Nama Pelanggan</th>
                <th>Nomor Telepon</th>
                <th>Berat (kg)</th>
                <th>Jenis Layanan</th>
                <th>Status Berat</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($pesanan as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->kode_pesanan }}</td>
                    <td>{{ $item->nama_pelanggan }}</td>
                    <td>{{ $item->nomor_telepon }}</td>
                    <td>{{ $item->berat_kg }}</td>
                    <td>{{ $item->nama_layanan }}</td>

                    <td>
                        @if ($item->berat_kg >= 10)
                            Besar
                        @else
                            Reguler
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('pesanan-laundry.edit', $item->id) }}">
                            Edit
                        </a>

                        <form action="{{ route('pesanan-laundry.destroy', $item->id) }}"
                            method="POST">
                            @csrf
                            @method('DELETE')

                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Data pesanan tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <hr>

    <h3>Keterangan Status Berat</h3>
    <ul>
        <li>Reguler: berat pesanan di bawah 10 kg.</li>
        <li>Besar: berat pesanan 10 kg atau lebih.</li>
    </ul>

</body>
</html>