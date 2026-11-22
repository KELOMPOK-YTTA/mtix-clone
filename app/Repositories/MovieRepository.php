<?php

namespace App\Repositories;

use App\Contracts\MovieInterface;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class MovieRepository
 *
 * SOLID - Single Responsibility Principle:
 * Bertanggung jawab penuh hanya untuk operasi database entitas Movie.
 * SOLID - Dependency Inversion Principle:
 * Mengimplementasikan MovieInterface.
 */
class MovieRepository implements MovieInterface
{
    /**
     * Ambil semua data film.
     *
     * @return Collection<int, Movie>
     */
    public function getAll(): Collection
    {
        return Movie::orderBy('release_date', 'desc')->get();
    }

    /**
     * Ambil film dengan status now_playing.
     *
     * @return Collection<int, Movie>
     */
    public function getNowPlaying(): Collection
    {
        return Movie::where('status', 'now_playing')
            ->orderBy('release_date', 'desc')
            ->get();
    }

    /**
     * Ambil film dengan status coming_soon.
     *
     * @return Collection<int, Movie>
     */
    public function getComingSoon(): Collection
    {
        return Movie::where('status', 'coming_soon')
            ->orderBy('release_date', 'asc')
            ->get();
    }

    /**
     * Cari film berdasarkan keyword pada judul atau genre.
     *
     * @return Collection<int, Movie>
     */
    public function searchByKeyword(string $keyword): Collection
    {
        return Movie::where(function ($query) use ($keyword) {
            $query->where('title', 'like', '%'.$keyword.'%')
                ->orWhere('genre', 'like', '%'.$keyword.'%');
        })
            ->orderBy('release_date', 'desc')
            ->get();
    }

    /**
     * Cari film berdasarkan slug.
     */
    public function findBySlug(string $slug): ?Movie
    {
        return Movie::where('slug', $slug)->first();
    }

    /**
     * Simpan film baru ke database.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Movie
    {
        return Movie::create($data);
    }

    /**
     * Perbarui data film tertentu.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Movie $movie, array $data): bool
    {
        return $movie->update($data);
    }

    /**
     * Hapus film dari database.
     */
    public function delete(Movie $movie): bool
    {
        return (bool) $movie->delete();
    }
}
