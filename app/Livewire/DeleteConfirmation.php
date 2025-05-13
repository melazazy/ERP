<?php

namespace App\Livewire;

use Livewire\Component;

class DeleteConfirmation extends Component
{
    public $show = false;
    public $itemId = null;
    public $message = 'Are you sure you want to delete this item?';
    public $type = 'item'; // 'item' or 'all'

    protected $listeners = ['confirmDelete'];

    public function confirmDelete($itemId = null, $type = 'item')
    {
        $this->itemId = $itemId;
        $this->type = $type;
        $this->show = true;
    }

    public function delete()
    {
        if ($this->type === 'all') {
            $this->emitUp('deleteAllConfirmed');
        } else {
            $this->emitUp('deleteConfirmed', $this->itemId);
        }
        $this->show = false;
    }

    public function cancel()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.delete-confirmation')->layout('layouts.app');
    }
}