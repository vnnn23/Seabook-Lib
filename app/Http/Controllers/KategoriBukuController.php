<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriBuku;
use Illuminate\Support\Facades\Auth;

class KategoriBukuController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategoribuku,nama_kategori',
        ]);

        KategoriBuku::create($request->only('nama_kategori'));

        // Redirect based on role: admin -> dashboardadmin, petugas -> dashboardpetugas
        $route = 'dashboardpetugas';
        if (Auth::check() && Auth::user()->role === 'admin') {
            $route = 'dashboardadmin';
        }

        return redirect()->route($route)->with('success', 'Kategori berhasil ditambahkan');
    }
}