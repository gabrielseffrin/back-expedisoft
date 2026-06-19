<?php

namespace Tests\Unit\DTOs\Order;

use App\DTOs\Order\FinishOrderDTO;
use Illuminate\Http\Request;
use Tests\TestCase;

class FinishOrderDTOTest extends TestCase
{
    public function test_it_creates_dto_with_justification(): void
    {
        $dto = new FinishOrderDTO(justification: 'Pacote danificado no transporte.');

        $this->assertSame('Pacote danificado no transporte.', $dto->justification);
    }

    public function test_it_creates_dto_with_null_justification(): void
    {
        $dto = new FinishOrderDTO();

        $this->assertNull($dto->justification);
    }

    public function test_from_request_extracts_justification(): void
    {
        $request = Request::create('/finish', 'POST', ['justification' => 'Carga incompleta.']);

        $dto = FinishOrderDTO::fromRequest($request);

        $this->assertSame('Carga incompleta.', $dto->justification);
    }

    public function test_from_request_with_no_justification(): void
    {
        $request = Request::create('/finish', 'POST', []);

        $dto = FinishOrderDTO::fromRequest($request);

        $this->assertNull($dto->justification);
    }

    public function test_dto_is_readonly(): void
    {
        $dto = new FinishOrderDTO(justification: 'test');

        $this->expectException(\Error::class);
        $dto->justification = 'changed'; // @phpstan-ignore-line
    }
}
