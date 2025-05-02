<?php

namespace App\Livewire;

trait FormResetTrait
{
    /**
     * Default reset properties
     * 
     * @var array
     */
    protected $defaultResetProperties = [
        'newTrust',
        'newRequisition',
        'selectedItems',
        'items',
        'itemSearch',
        'users',
        'departments',
        'requestedBySearch',
        'departmentSearch'
    ];
    
    /**
     * Reset form properties
     */
    public function resetForm()
    {
        $this->reset($this->getResetProperties());
        $this->resetFormSpecificProperties();
    }

    /**
     * Get reset properties
     *
     * @return array
     */
    protected function getResetProperties(): array
    {
        return $this->resetProperties ?? $this->defaultResetProperties;
    }

    /**
     * Reset form-specific properties
     */
    protected function resetFormSpecificProperties()
    {
        // Override in child classes if needed
    }
}