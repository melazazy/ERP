<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $itemId;
    protected $requestedQuantity;
    protected $availableQuantity;

    public function __construct(string $message = "", int $itemId = 0, float $requestedQuantity = 0, float $availableQuantity = 0)
    {
        parent::__construct($message);
        $this->itemId = $itemId;
        $this->requestedQuantity = $requestedQuantity;
        $this->availableQuantity = $availableQuantity;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getRequestedQuantity(): float
    {
        return $this->requestedQuantity;
    }

    public function getAvailableQuantity(): float
    {
        return $this->availableQuantity;
    }

    public function getContext(): array
    {
        return [
            'item_id' => $this->itemId,
            'requested_quantity' => $this->requestedQuantity,
            'available_quantity' => $this->availableQuantity,
            'shortfall' => $this->requestedQuantity - $this->availableQuantity
        ];
    }
}
