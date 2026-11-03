<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pesanan Laundry</title>
</head>
<body>

    <h1>EDIT PESANAN LAUNDRY</h1>

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

    <form action="{{ route('pesanan-laundry.update', $pesanan->id) }}"
        method="POST">

        @csrf
        @method('PUT')

        <p>
            <label>Kode Pesanan:</label><br>
            <input type="text" name="kode_pesanan"
                value="{{ old('kode_pesanan', $pesanan->kode_pesanan) }}"
                maxlength="10" required>
        </p>

        <p>
            <label>Nama Pelanggan:</label><br>
            <input type="text" name="nama_pelanggan"
                value="{{ old('nama_pelanggan', $pesanan->nama_pelanggan) }}"
                minlength="3" maxlength="100" required>
        </p>

        <p>
            <label>Nomor Telepon:</label><br>
            <input type="text" name="nomor_telepon"
                value="{{ old('nomor_telepon', $pesanan->nomor_telepon) }}"
                minlength="10" maxlength="20" required>
        </p>

        <p>
            <label>Berat Laundry (kg):</label><br>
            <input type="number" name="berat_kg"
                value="{{ old('berat_kg', $pesanan->berat_kg) }}"
                min="1" max="20" required>
        </p>

        <p>
            <label>Jenis Layanan:</label><br>
            <select name="jenis_layanan_id" required>
                @foreach ($layananList as $layanan)
                    <option value="{{ $layanan->id }}"
                        {{ old('jenis_layanan_id', $pesanan->jenis_layanan_id) == $layanan->id ? 'selected' : '' }}>
                        {{ $layanan->nama_layanan }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Perubahan</button>
        <a href="{{ route('pesanan-laundry.index') }}">Kembali</a>

    </form>

</body>
</html>