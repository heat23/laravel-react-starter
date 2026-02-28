<?php

namespace App\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionException extends RuntimeException
{
    public function __construct(
        string $message,
        private int $statusCode = Response::HTTP_BAD_REQUEST,
    ) {
        parent::__construct($message);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->statusCode);
    }

    public static function noActiveSubscription(): self
    {
        return new self('No active subscription to cancel.', Response::HTTP_BAD_REQUEST);
    }

    public static function noCanceledSubscription(): self
    {
        return new self('No canceled subscription to resume.', Response::HTTP_BAD_REQUEST);
    }

    public static function operationFailed(): self
    {
        return new self('Unable to process your request. Please try again.', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
