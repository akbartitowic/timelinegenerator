<?php

namespace App\Livewire;

use App\Models\Holiday;
use App\Services\WorkingDaysService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Holidays')]
class HolidaysPage extends Component
{
    public string $newDate = '';
    public string $newName = '';

    protected $rules = [
        'newDate' => 'required|date|unique:holidays,date',
        'newName' => 'nullable|string|max:255',
    ];

    public function add(WorkingDaysService $workingDays): void
    {
        $this->validate();
        Holiday::create(['date' => $this->newDate, 'name' => $this->newName ?: null]);
        $workingDays->reload();
        $this->reset(['newDate', 'newName']);
        $this->dispatch('holiday-updated');
    }

    public function remove(int $id, WorkingDaysService $workingDays): void
    {
        Holiday::findOrFail($id)->delete();
        $workingDays->reload();
        $this->dispatch('holiday-updated');
    }

    public function render()
    {
        return view('livewire.holidays-page', [
            'holidays' => Holiday::orderBy('date')->get(),
        ]);
    }
}
