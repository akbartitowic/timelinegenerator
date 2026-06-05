<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Projects')]
class ProjectsPage extends Component
{
    public bool   $showModal  = false;
    public ?int   $editingId  = null;
    public string $name       = '';
    public string $startDate  = '';
    public string $deadline   = '';
    public string $description = '';

    protected $rules = [
        'name'      => 'required|string|max:255',
        'startDate' => 'required|date',
        'deadline'  => 'required|date|after_or_equal:startDate',
    ];

    public function openCreate(): void
    {
        $this->reset(['name', 'startDate', 'deadline', 'description', 'editingId']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $project = Project::findOrFail($id);
        $this->editingId   = $id;
        $this->name        = $project->name;
        $this->startDate   = $project->start_date->format('Y-m-d');
        $this->deadline    = $project->deadline->format('Y-m-d');
        $this->description = $project->description ?? '';
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'start_date'  => $this->startDate,
            'deadline'    => $this->deadline,
            'description' => $this->description ?: null,
        ];

        if ($this->editingId) {
            Project::findOrFail($this->editingId)->update($data);
        } else {
            Project::create($data);
        }

        $this->showModal = false;
        $this->reset(['name', 'startDate', 'deadline', 'description', 'editingId']);
    }

    public function delete(int $id): void
    {
        Project::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.projects-page', [
            'projects' => Project::latest()->get(),
        ]);
    }
}
