<?php

namespace App\Exceptions;

use Exception;

class DuplicateDocumentException extends Exception
{
    protected $documentNumber;
    protected $documentType;

    public function __construct(string $message = "", string $documentNumber = "", string $documentType = "")
    {
        parent::__construct($message);
        $this->documentNumber = $documentNumber;
        $this->documentType = $documentType;
    }

    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }

    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    public function getContext(): array
    {
        return [
            'document_number' => $this->documentNumber,
            'document_type' => $this->documentType
        ];
    }
}
