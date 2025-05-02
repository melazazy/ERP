<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Subcategory;
use Livewire\Component;

class ManagementSubcategory extends Component
{
    public $subcategories;
    public $categories;
    public $newSubcategory = [
        'id' => null,
        'name' => '',
        'description' => '',
        'category_id' => '',
    ];

    public function mount()
    {
        $this->categories = Category::all();
        $this->refreshSubcategories();
    }

    public function refreshSubcategories()
    {
        $this->subcategories = Subcategory::with('category')->get();
    }

    public function addSubcategory()
    {
        $this->validate([
            'newSubcategory.name' => 'required|string|max:255',
            'newSubcategory.description' => 'nullable|string',
            'newSubcategory.category_id' => 'required|exists:categories,id',
        ]);

        Subcategory::create([
            'name' => $this->newSubcategory['name'],
            'description' => $this->newSubcategory['description'],
            'category_id' => $this->newSubcategory['category_id'],
        ]);

        $this->reset('newSubcategory');
        $this->refreshSubcategories();
        session()->flash('message', 'Subcategory added successfully.');
    }

    public function editSubcategory($id)
    {
        $subcategory = Subcategory::find($id);
        $this->newSubcategory = [
            'id' => $subcategory->id,
            'name' => $subcategory->name,
            'description' => $subcategory->description,
            'category_id' => $subcategory->category_id,
        ];
    }

    public function updateSubcategory()
    {
        $this->validate([
            'newSubcategory.name' => 'required|string|max:255',
            'newSubcategory.description' => 'nullable|string',
            'newSubcategory.category_id' => 'required|exists:categories,id',
        ]);

        $subcategory = Subcategory::find($this->newSubcategory['id']);
        $subcategory->update([
            'name' => $this->newSubcategory['name'],
            'description' => $this->newSubcategory['description'],
            'category_id' => $this->newSubcategory['category_id'],
        ]);

        $this->reset('newSubcategory');
        $this->refreshSubcategories();
        session()->flash('message', 'Subcategory updated successfully.');
    }

    public function deleteSubcategory($id)
    {
        Subcategory::destroy($id);
        $this->refreshSubcategories();
        session()->flash('message', 'Subcategory deleted successfully.');
    }

    public function render()
    {
        return view('livewire.management-subcategory')->layout('layouts.app');
    }
}