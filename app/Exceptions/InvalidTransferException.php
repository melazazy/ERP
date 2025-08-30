<?php

namespace App\Exceptions;

use Exception;

class InvalidTransferException extends Exception
{
    protected $fromDepartmentId;
    protected $toDepartmentId;
    protected $itemId;
    protected $quantity;

    public function __construct(string $message = "", int $fromDepartmentId = 0, int $toDepartmentId = 0, int $itemId = 0, float $quantity = 0)
    {
        parent::__construct($message);
        $this->fromDepartmentId = $fromDepartmentId;
        $this->toDepartmentId = $toDepartmentId;
        $this->itemId = $itemId;
        $this->quantity = $quantity;
    }

    public function getFromDepartmentId(): int
    {
        return $this->fromDepartmentId;
    }

    public function getToDepartmentId(): int
    {
        return $this->toDepartmentId;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getContext(): array
    {
        return [
            'from_department_id' => $this->fromDepartmentId,
            'to_department_id' => $this->toDepartmentId,
            'item_id' => $this->itemId,
            'quantity' => $this->quantity
        ];
    }
}
