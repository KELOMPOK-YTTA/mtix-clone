<?php

namespace App\Exceptions;

use Exception;

/**
 * Error bisnis pada modul pembayaran (booking kedaluwarsa, sudah dibayar, dll.).
 * Controller mengubahnya menjadi response JSON 422.
 */
class PaymentException extends Exception {}
