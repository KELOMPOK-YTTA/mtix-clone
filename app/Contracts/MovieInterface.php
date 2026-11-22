<?php

namespace App\Contracts;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface MovieInterface
 *
 * SOLID - Interface Segregation Principle & Dependency Inversion Principle:
 * Kontrak akses dan manipulasi data entitas Movie pada database Cinema XXI.
 */
interface MovieInterface
{
    /**
     * Ambil semua data film.
     *
     * @return Collection<int, Movie>
     */
    public function getAll(): Collection;

    /**
     * Ambil film dengan status now_playing.
     *
     * @return Collection<int, Movie>
     */
    public function getNowPlaying(): Collection;

    /**
     * Ambil film dengan status coming_soon.
     *
     * @return Collection<int, Movie>
     */
    public function getComingSoon(): Collection;

    /**
     * Cari film berdasarkan keyword pada judul atau genre.
     *
     * @return Collection<int, Movie>
     */
    public function searchByKeyword(string $keyword): Collection;

    /**
     * Cari film berdasarkan slug.
     */
    public function findBySlug(string $slug): ?Movie;

    /**
     * Simpan film baru ke database.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Movie;

    /**
     * Perbarui data film tertentu.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Movie $movie, array $data): bool;

    /**
     * Hapus film dari database.
     */
    public function delete(Movie $movie): bool;
}
