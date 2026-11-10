@include('partials.motion')
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Buku</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('books.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="judul" class="form-control" value="{{ old('judul') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Penulis</label>
            <input type="text" name="penulis" class="form-control" value="{{ old('penulis') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Penerbit</label>
            <input type="text" name="penerbit" class="form-control" value="{{ old('penerbit') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Tahun Terbit</label>
            <input type="number" name="tahun_terbit" class="form-control" value="{{ old('tahun_terbit') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">ISBN</label>
            <input type="text" name="isbn" class="form-control" value="{{ old('isbn') }}" required placeholder="Contoh: 978-602-xxx atau 'dalam proses'">
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah Total Eksemplar</label>
            <input type="number" name="jumlah_total" class="form-control" value="{{ old('jumlah_total', 1) }}" min="0" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="kategoris" class="form-control" required>
                <option value="">Pilih Kategori...</option>
                @foreach($kategoris as $kategori)
                <option value="{{ $kategori->KategoriID }}" {{ old('kategoris') == $kategori->KategoriID ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Cover Buku</label>
            <input type="file" name="cover" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('books') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
