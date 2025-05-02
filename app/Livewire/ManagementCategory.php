<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;

class ManagementCategory extends Component
{
    public $categories;
    public $newCategory = [
        'id' => null,
        'name' => '',
        'description' => '',
    ];

    public function mount()
    {
        $this->refreshCategories();
    }

    public function refreshCategories()
    {
        $this->categories = Category::all();
    }

    public function addCategory()
    {
        $this->validate([
            'newCategory.name' => 'required|string|max:255|unique:categories,name',
            'newCategory.description' => 'nullable|string',
        ]);

        Category::create([
            'name' => $this->newCategory['name'],
            'description' => $this->newCategory['description'],
        ]);

        $this->reset('newCategory');
        $this->refreshCategories();
        session()->flash('message', 'Category added successfully.');
    }

    public function editCategory($id)
    {
        $category = Category::find($id);
        $this->newCategory = [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
        ];
    }

    public function updateCategory()
    {
        $this->validate([
            'newCategory.name' => 'required|string|max:255|unique:categories,name,' . $this->newCategory['id'],
            'newCategory.description' => 'nullable|string',
        ]);

        $category = Category::find($this->newCategory['id']);
        $category->update([
            'name' => $this->newCategory['name'],
            'description' => $this->newCategory['description'],
        ]);

        $this->reset('newCategory');
        $this->refreshCategories();
        session()->flash('message', 'Category updated successfully.');
    }

    public function deleteCategory($id)
    {
        Category::destroy($id);
        $this->refreshCategories();
        session()->flash('message', 'Category deleted successfully.');
    }

    public function render()
    {
        return view('livewire.management-category')->layout('layouts.app');
    }
}