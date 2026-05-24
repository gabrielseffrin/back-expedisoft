<?php

namespace App\DTOs\Order;

use Illuminate\Http\Request;

readonly class FinishOrderDTO
{
    public function __construct(
        public ?string $justification = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            justification: $request->input('justification'),
        );
    }
}
