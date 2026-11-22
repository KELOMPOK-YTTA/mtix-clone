<?php

namespace App\Services;

use App\Contracts\MovieInterface;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class MovieService
 *
 * SOLID - Single Responsibility Principle:
 * Mengisolasi business logic manajemen film (pencarian, filter status, pengelolaan poster).
 * SOLID - Dependency Inversion Principle:
 * Bergantung pada abstraksi (MovieInterface), bukan implementasi konkrit database.
 */
class MovieService
{
    /**
     * Constructor injection menerapkan DIP (Dependency Inversion Principle).
     */
    public function __construct(
        private readonly MovieInterface $movieRepository
    ) {}

    /**
     * Ambil daftar film berdasarkan filter status dan/atau keyword pencarian.
     *
     * @return Collection<int, Movie>
     */
    public function getMovies(?string $status = null, ?string $keyword = null): Collection
    {
        if ($keyword !== null && $keyword !== '') {
            return $this->movieRepository->searchByKeyword($keyword);
        }

        return match ($status) {
            'now_playing' => $this->movieRepository->getNowPlaying(),
            'coming_soon' => $this->movieRepository->getComingSoon(),
            default => $this->movieRepository->getAll(),
        };
    }

    /**
     * Ambil detail film berdasarkan slug.
     */
    public function findBySlug(string $slug): ?Movie
    {
        return $this->movieRepository->findBySlug($slug);
    }

    /**
     * Buat film baru beserta penanganan upload poster.
     *
     * @param  array<string, mixed>  $data
     */
    public function createMovie(array $data): Movie
    {
        $data['slug'] = $this->generateUniqueSlug($data['title']);

        if (isset($data['poster']) && $data['poster'] instanceof UploadedFile) {
            $data['poster_url'] = $this->uploadPoster($data['poster']);
        }

        unset($data['poster']);

        return $this->movieRepository->create($data);
    }

    /**
     * Perbarui data film beserta penanganan upload poster baru jika ada.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateMovie(Movie $movie, array $data): Movie
    {
        if (isset($data['title']) && $data['title'] !== $movie->title) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $movie->id);
        }

        if (isset($data['poster']) && $data['poster'] instanceof UploadedFile) {
            $this->deletePosterFile($movie->poster_url);
            $data['poster_url'] = $this->uploadPoster($data['poster']);
        }

        unset($data['poster']);

        $this->movieRepository->update($movie, $data);

        return $movie->fresh();
    }

    /**
     * Hapus film beserta file poster-nya dari storage.
     */
    public function deleteMovie(Movie $movie): bool
    {
        $this->deletePosterFile($movie->poster_url);

        return $this->movieRepository->delete($movie);
    }

    /**
     * Generate slug unik dari title film.
     * Jika slug sudah ada di database, tambahkan sufiks angka agar tetap unik.
     */
    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Cek apakah slug sudah digunakan oleh film lain.
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Movie::where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Upload file poster ke Storage::disk('public') di folder posters/.
     * Mengembalikan URL publik yang dapat diakses.
     */
    private function uploadPoster(UploadedFile $file): string
    {
        $path = $file->store('posters', 'public');

        return Storage::disk('public')->url($path);
    }

    /**
     * Hapus file poster dari storage jika ada.
     */
    private function deletePosterFile(?string $posterUrl): void
    {
        if ($posterUrl === null || $posterUrl === '') {
            return;
        }

        // Ekstrak relative path dari URL untuk operasi Storage
        $relativePath = ltrim(parse_url($posterUrl, PHP_URL_PATH) ?? '', '/');
        $storageRelativePath = str_replace('storage/', '', $relativePath);

        if (Storage::disk('public')->exists($storageRelativePath)) {
            Storage::disk('public')->delete($storageRelativePath);
        }
    }
}
