<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Http\Resources\MovieResource;
use App\Services\MovieService;
use Illuminate\Http\JsonResponse;

/**
 * Class AdminMovieController
 *
 * SOLID - Single Responsibility Principle:
 * Mengatur interaksi HTTP untuk operasi CRUD film oleh administrator.
 * SOLID - Dependency Inversion Principle:
 * Menginjeksi MovieService ke dalam konstruktor.
 */
class AdminMovieController extends Controller
{
    public function __construct(
        private readonly MovieService $movieService
    ) {}

    /**
     * POST /api/admin/movies
     * Membuat film baru beserta upload poster (opsional).
     */
    public function store(StoreMovieRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster');
        }

        $movie = $this->movieService->createMovie($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Film berhasil ditambahkan.',
            'data' => new MovieResource($movie),
        ], 201);
    }

    /**
     * PUT /api/admin/movies/{slug}
     * Memperbarui data film yang sudah ada, termasuk mengganti poster jika diupload.
     */
    public function update(UpdateMovieRequest $request, string $slug): JsonResponse
    {
        $movie = $this->movieService->findBySlug($slug);

        if ($movie === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Film tidak ditemukan.',
            ], 404);
        }

        $data = $request->validated();

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster');
        }

        $updatedMovie = $this->movieService->updateMovie($movie, $data);

        return response()->json([
            'status' => 'success',
            'message' => 'Film berhasil diperbarui.',
            'data' => new MovieResource($updatedMovie),
        ]);
    }

    /**
     * DELETE /api/admin/movies/{slug}
     * Menghapus film beserta file poster-nya dari storage.
     */
    public function destroy(string $slug): JsonResponse
    {
        $movie = $this->movieService->findBySlug($slug);

        if ($movie === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Film tidak ditemukan.',
            ], 404);
        }

        $this->movieService->deleteMovie($movie);

        return response()->json([
            'status' => 'success',
            'message' => 'Film berhasil dihapus.',
        ]);
    }
}
