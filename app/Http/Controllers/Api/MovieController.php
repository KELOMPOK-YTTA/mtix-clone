<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Services\MovieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MovieController
 *
 * SOLID - Single Responsibility Principle:
 * Mengatur interaksi HTTP publik untuk membaca data film (listing dan detail).
 * SOLID - Dependency Inversion Principle:
 * Menginjeksi MovieService ke dalam konstruktor.
 */
class MovieController extends Controller
{
    public function __construct(
        private readonly MovieService $movieService
    ) {}

    /**
     * GET /api/movies
     * Menampilkan daftar film dengan filter opsional status dan pencarian keyword.
     *
     * Query params:
     *   ?status=now_playing|coming_soon  → filter berdasarkan status tayang
     *   ?search=keyword                  → pencarian berdasarkan judul atau genre
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $keyword = $request->query('search');

        $movies = $this->movieService->getMovies($status, $keyword);

        return response()->json([
            'status' => 'success',
            'message' => 'Data film berhasil diambil.',
            'data' => MovieResource::collection($movies),
        ]);
    }

    /**
     * GET /api/movies/{slug}
     * Menampilkan detail satu film berdasarkan slug.
     */
    public function show(string $slug): JsonResponse
    {
        $movie = $this->movieService->findBySlug($slug);

        if ($movie === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Film tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail film berhasil diambil.',
            'data' => new MovieResource($movie),
        ]);
    }
}
