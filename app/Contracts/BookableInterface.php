<?php

namespace App\Contracts;

use App\Models\Booking;
use App\Models\User;

interface BookableInterface
{
    public function lockSeats(int $scheduleId, array $seatIds, User $user): Booking;

    public function confirmBooking(int $bookingId, User $user): Booking;

    public function cancelBooking(int $bookingId, User $user): Booking;

    public function releaseExpiredBookings(): int;
}
