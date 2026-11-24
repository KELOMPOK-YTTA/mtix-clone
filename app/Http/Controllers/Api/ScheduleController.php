<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class ScheduleController
 *
 * SOLID - Single Responsibility Principle:
 * Mengatur interaksi HTTP untuk jadwal tayang (listing, detail, layout kursi, CRUD admin).
 * SOLID - Dependency Inversion Principle:
 * Menginjeksi ScheduleService ke dalam konstruktor.
 */
class ScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleService $scheduleService
    ) {}

    /**
     * GET /api/schedules
     * Menampilkan daftar jadwal tayang dengan filter opsional.
     *
     * Query params:
     *   ?movie_id=1          → jadwal untuk satu film
     *   ?cinema_id=1         → jadwal di satu bioskop
     *   ?studio_id=1         → jadwal di satu studio
     *   ?city=Pontianak      → jadwal di satu kota
     *   ?date=2026-11-27     → jadwal pada tanggal tertentu (default: hari ini dan seterusnya)
     *   ?time_from=17:00     → jadwal mulai jam tertentu ke atas
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'movie_id' => ['nullable', 'integer'],
            'cinema_id' => ['nullable', 'integer'],
            'studio_id' => ['nullable', 'integer'],
            'city' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'time_from' => ['nullable', 'date_format:H:i'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Filter jadwal tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $schedules = $this->scheduleService->getSchedules($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data jadwal berhasil diambil.',
            'data' => ScheduleResource::collection($schedules),
        ]);
    }

    /**
     * GET /api/schedules/{id}
     * Menampilkan detail satu jadwal.
     */
    public function show(int $id): JsonResponse
    {
        $schedule = $this->scheduleService->findById($id);

        if ($schedule === null) {
            return $this->notFoundResponse();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail jadwal berhasil diambil.',
            'data' => new ScheduleResource($schedule),
        ]);
    }

    /**
     * GET /api/schedules/{id}/seats
     * Menampilkan layout kursi beserta status (available/booked/taken) untuk satu jadwal.
     */
    public function seats(int $id): JsonResponse
    {
        $schedule = $this->scheduleService->findById($id);

        if ($schedule === null) {
            return $this->notFoundResponse();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Layout kursi berhasil diambil.',
            'data' => [
                'schedule' => new ScheduleResource($schedule),
                'rows' => $this->scheduleService->getSeatMap($schedule),
            ],
        ]);
    }

    /**
     * POST /api/admin/schedules
     * Membuat jadwal baru sekaligus menyalin kursi studio ke jadwal tersebut.
     */
    public function store(StoreScheduleRequest $request): JsonResponse
    {
        $schedule = $this->scheduleService->createSchedule($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil dibuat.',
            'data' => new ScheduleResource($schedule),
        ], 201);
    }

    /**
     * PUT /api/admin/schedules/{id}
     * Mengubah jadwal. Film/studio/tanggal/jam terkunci jika sudah ada kursi dipesan.
     */
    public function update(UpdateScheduleRequest $request, int $id): JsonResponse
    {
        $schedule = $this->scheduleService->findById($id);

        if ($schedule === null) {
            return $this->notFoundResponse();
        }

        $schedule = $this->scheduleService->updateSchedule($schedule, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil diperbarui.',
            'data' => new ScheduleResource($schedule),
        ]);
    }

    /**
     * DELETE /api/admin/schedules/{id}
     * Menghapus jadwal (ditolak jika sudah ada kursi dipesan).
     */
    public function destroy(int $id): JsonResponse
    {
        $schedule = $this->scheduleService->findById($id);

        if ($schedule === null) {
            return $this->notFoundResponse();
        }

        $this->scheduleService->deleteSchedule($schedule);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil dihapus.',
        ]);
    }

    private function notFoundResponse(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Jadwal tidak ditemukan.',
        ], 404);
    }
}
