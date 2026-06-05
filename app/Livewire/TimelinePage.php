<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Task;
use App\Services\DependencyService;
use App\Services\WorkingDaysService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Timeline')]
class TimelinePage extends Component
{
    public Project $project;

    public bool  $showModal       = false;
    public ?int  $editingTaskId   = null;
    public ?int  $parentTaskId    = null;

    public string  $taskName          = '';
    public string  $taskStartDate     = '';
    public string  $taskEndDate       = '';
    public string  $taskEndDateMode   = 'date';   // 'date' | 'duration'
    public int     $taskDurationInput = 1;         // working days when mode = 'duration'
    public string  $taskStatus        = 'not_started';
    public string  $taskOwner         = '';
    public string  $taskColor         = '#3B82F6';
    public string  $taskNotes         = '';
    public int     $taskProgress      = 0;
    public string  $taskDependsOn     = '';
    public int     $taskOffsetDays    = 1;

    protected function rules(): array
    {
        return [
            'taskName'          => 'required|string|max:255',
            'taskStartDate'     => 'nullable|date',
            'taskEndDate'       => $this->taskEndDateMode === 'date'
                                    ? 'nullable|date|after_or_equal:taskStartDate'
                                    : 'nullable',
            'taskDurationInput' => $this->taskEndDateMode === 'duration'
                                    ? 'required|integer|min:1'
                                    : 'nullable|integer',
            'taskStatus'        => 'required|in:not_started,in_progress,completed,on_hold',
            'taskOwner'         => 'nullable|string|max:255',
            'taskColor'         => 'required|string',
            'taskProgress'      => 'integer|min:0|max:100',
            'taskOffsetDays'    => 'integer|min:1',
        ];
    }

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function openAddTask(): void
    {
        $this->resetTaskForm();
        $this->parentTaskId  = null;
        $this->editingTaskId = null;
        $this->showModal     = true;
        $this->dispatch('modal-opened', startDate: '', endDate: '');
    }

    public function openAddSubtask(int $parentId): void
    {
        $this->resetTaskForm();
        $this->parentTaskId  = $parentId;
        $this->editingTaskId = null;
        $this->showModal     = true;
        $this->dispatch('modal-opened', startDate: '', endDate: '');
    }

    public function openEditTask(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $this->editingTaskId   = $taskId;
        $this->parentTaskId    = $task->parent_id;
        $this->taskName        = $task->name;
        $this->taskStartDate   = $task->start_date?->format('Y-m-d') ?? '';
        $this->taskEndDate     = $task->end_date?->format('Y-m-d') ?? '';
        $this->taskStatus      = $task->status;
        $this->taskOwner       = $task->task_owner ?? '';
        $this->taskColor       = $task->color;
        $this->taskNotes       = $task->notes ?? '';
        $this->taskProgress    = $task->progress;
        $this->taskDependsOn    = $task->depends_on_task_id ? (string) $task->depends_on_task_id : '';
        $this->taskOffsetDays   = $task->offset_days;
        // Restore end-date mode: if duration matches, default to 'duration' mode
        $this->taskDurationInput = $task->duration_days ?: 1;
        $this->taskEndDateMode   = 'date';
        $this->showModal        = true;
        $this->dispatch('modal-opened', startDate: $this->taskStartDate, endDate: $this->taskEndDate);
    }

    public function saveTask(WorkingDaysService $workingDays, DependencyService $depService): void
    {
        $this->validate();

        $startDate = $this->taskStartDate ? Carbon::parse($this->taskStartDate) : null;

        // Resolve end date based on mode
        if ($this->taskEndDateMode === 'duration' && $startDate && $this->taskDurationInput > 0) {
            $endDate  = $workingDays->addWorkingDays($startDate, $this->taskDurationInput - 1);
            $duration = $this->taskDurationInput;
        } else {
            $endDate  = $this->taskEndDate ? Carbon::parse($this->taskEndDate) : null;
            $duration = ($startDate && $endDate) ? $workingDays->countWorkingDays($startDate, $endDate) : 0;
        }

        $dependsOnId = $this->taskDependsOn ? (int) $this->taskDependsOn : null;

        // Cycle detection
        if ($dependsOnId && $this->editingTaskId) {
            if ($depService->detectCycle($this->editingTaskId, $dependsOnId)) {
                $this->addError('taskDependsOn', 'Dependency ini akan membuat siklus.');
                return;
            }
        }

        // Max sort_order for new tasks
        $maxOrder = Task::where('project_id', $this->project->id)
            ->where('parent_id', $this->parentTaskId)
            ->max('sort_order') ?? 0;

        $data = [
            'project_id'         => $this->project->id,
            'parent_id'          => $this->parentTaskId,
            'name'               => $this->taskName,
            'start_date'         => $startDate?->format('Y-m-d'),
            'end_date'           => $endDate?->format('Y-m-d'),
            'duration_days'      => $duration,
            'status'             => $this->taskStatus,
            'task_owner'         => $this->taskOwner ?: null,
            'color'              => $this->taskColor,
            'notes'              => $this->taskNotes ?: null,
            'progress'           => $this->taskProgress,
            'depends_on_task_id' => $dependsOnId,
            'offset_days'        => $this->taskOffsetDays,
        ];

        $oldEndDate = null;
        if ($this->editingTaskId) {
            $task = Task::findOrFail($this->editingTaskId);
            $oldEndDate = $task->end_date?->format('Y-m-d');
            $task->update($data);
        } else {
            $data['sort_order'] = $maxOrder + 1;
            $task = Task::create($data);
        }

        // Cascade if end date changed
        if ($endDate && $endDate->format('Y-m-d') !== $oldEndDate) {
            $depService->cascade($task->fresh());
        }

        $this->showModal = false;
        $this->resetTaskForm();
    }

    public function deleteTask(int $taskId): void
    {
        Task::findOrFail($taskId)->delete();
    }

    public function updateStatus(int $taskId, string $status): void
    {
        Task::findOrFail($taskId)->update(['status' => $status]);
    }

    public function moveTaskUp(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $prev = Task::where('project_id', $this->project->id)
            ->where('parent_id', $task->parent_id)
            ->where('sort_order', '<', $task->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
        if ($prev) {
            [$task->sort_order, $prev->sort_order] = [$prev->sort_order, $task->sort_order];
            $task->save();
            $prev->save();
        }
    }

    public function moveTaskDown(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $next = Task::where('project_id', $this->project->id)
            ->where('parent_id', $task->parent_id)
            ->where('sort_order', '>', $task->sort_order)
            ->orderBy('sort_order')
            ->first();
        if ($next) {
            [$task->sort_order, $next->sort_order] = [$next->sort_order, $task->sort_order];
            $task->save();
            $next->save();
        }
    }

    public function reorderTasks(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Task::where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    public function updatedTaskDependsOn(WorkingDaysService $workingDays): void
    {
        $this->recalcDatesFromDependency($workingDays);
    }

    public function updatedTaskOffsetDays(WorkingDaysService $workingDays): void
    {
        if ($this->taskDependsOn) {
            $this->recalcDatesFromDependency($workingDays);
        }
    }

    public function updatedTaskDurationInput(WorkingDaysService $workingDays): void
    {
        if ($this->taskEndDateMode === 'duration') {
            $this->recalcEndDateFromDuration($workingDays);
        }
    }

    public function updatedTaskStartDate(WorkingDaysService $workingDays): void
    {
        if ($this->taskEndDateMode === 'duration' && $this->taskDurationInput > 0) {
            $this->recalcEndDateFromDuration($workingDays);
        }
    }

    public function setEndDateMode(string $mode, WorkingDaysService $workingDays): void
    {
        $this->taskEndDateMode = $mode;
        if ($mode === 'duration' && $this->taskStartDate && $this->taskDurationInput > 0) {
            $this->recalcEndDateFromDuration($workingDays);
        }
    }

    public function updatedTaskEndDateMode(WorkingDaysService $workingDays): void
    {
        if ($this->taskEndDateMode === 'duration' && $this->taskStartDate && $this->taskDurationInput > 0) {
            $this->recalcEndDateFromDuration($workingDays);
        }
    }

    private function recalcEndDateFromDuration(WorkingDaysService $workingDays): void
    {
        if (!$this->taskStartDate || $this->taskDurationInput < 1) return;

        $start  = Carbon::parse($this->taskStartDate);
        $newEnd = $workingDays->addWorkingDays($start, $this->taskDurationInput - 1);
        $this->taskEndDate = $newEnd->format('Y-m-d');
        $this->dispatch('dates-updated', startDate: $this->taskStartDate, endDate: $this->taskEndDate);
    }

    private function recalcDatesFromDependency(WorkingDaysService $workingDays): void
    {
        if (!$this->taskDependsOn) return;

        $sourceTask = Task::find((int) $this->taskDependsOn);
        if (!$sourceTask || !$sourceTask->end_date) return;

        $offset   = max(1, $this->taskOffsetDays);
        $newStart = $workingDays->addWorkingDays(
            Carbon::parse($sourceTask->end_date), $offset
        );

        $this->taskStartDate = $newStart->format('Y-m-d');

        if ($this->taskEndDateMode === 'duration' && $this->taskDurationInput > 0) {
            // Recalculate end from duration
            $newEnd = $workingDays->addWorkingDays($newStart, $this->taskDurationInput - 1);
            $this->taskEndDate = $newEnd->format('Y-m-d');
        } elseif ($this->taskEndDate) {
            // Date mode: if end is before new start, push it forward
            if (Carbon::parse($this->taskEndDate)->lt($newStart)) {
                $this->taskEndDate = $newStart->format('Y-m-d');
            }
        }

        $this->dispatch('dates-updated', startDate: $this->taskStartDate, endDate: $this->taskEndDate);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetTaskForm();
    }

    private function resetTaskForm(): void
    {
        $this->taskName          = '';
        $this->taskStartDate     = '';
        $this->taskEndDate       = '';
        $this->taskEndDateMode   = 'date';
        $this->taskDurationInput = 1;
        $this->taskStatus        = 'not_started';
        $this->taskOwner         = '';
        $this->taskColor         = '#3B82F6';
        $this->taskNotes         = '';
        $this->taskProgress      = 0;
        $this->taskDependsOn     = '';
        $this->taskOffsetDays    = 1;
        $this->resetErrorBag();
    }

    public function getHolidaysJson(WorkingDaysService $workingDays): string
    {
        return json_encode($workingDays->getHolidayDatesForFrontend());
    }

    public function render(WorkingDaysService $workingDays)
    {
        $tasks = Task::where('project_id', $this->project->id)
            ->whereNull('parent_id')
            ->with(['subtasks' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        // All tasks for the dependency dropdown (top-level + subtasks, grouped)
        $allTasksForDropdown = Task::where('project_id', $this->project->id)
            ->whereNull('parent_id')
            ->with(['subtasks' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.timeline-page', [
            'tasks'               => $tasks,
            'allTasksForDropdown' => $allTasksForDropdown,
            'holidaysJson'        => $workingDays->getHolidayDatesForFrontend(),
        ]);
    }
}
