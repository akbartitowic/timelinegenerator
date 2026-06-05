<?php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DependencyService
{
    public function __construct(private WorkingDaysService $workingDays) {}

    public function cascade(Task $changedTask): array
    {
        $updated = [];
        $queue   = [$changedTask->id];
        $visited = [];

        DB::transaction(function () use (&$queue, &$visited, &$updated) {
            while (!empty($queue)) {
                $sourceId = array_shift($queue);
                if (isset($visited[$sourceId])) continue;
                $visited[$sourceId] = true;

                $source     = Task::find($sourceId);
                $dependents = Task::where('depends_on_task_id', $sourceId)->get();

                foreach ($dependents as $dep) {
                    if (!$source->end_date) continue;

                    $newStart = $this->workingDays->addWorkingDays(
                        Carbon::parse($source->end_date),
                        max(1, $dep->offset_days)
                    );

                    if ($newStart->equalTo(Carbon::parse($dep->start_date))) continue;

                    $duration = max(1, $dep->duration_days);
                    $newEnd   = $this->workingDays->addWorkingDays($newStart, $duration - 1);

                    $dep->update([
                        'start_date' => $newStart->format('Y-m-d'),
                        'end_date'   => $newEnd->format('Y-m-d'),
                    ]);

                    $updated[] = $dep->id;
                    $queue[]   = $dep->id;
                }
            }
        });

        return $updated;
    }

    public function detectCycle(int $taskId, int $proposedDependsOnId): bool
    {
        if ($taskId === $proposedDependsOnId) return true;

        $visited = [];
        $queue   = [$proposedDependsOnId];

        while (!empty($queue)) {
            $current = array_shift($queue);
            if ($current === $taskId) return true;
            if (isset($visited[$current])) continue;
            $visited[$current] = true;

            $dep = Task::find($current);
            if ($dep && $dep->depends_on_task_id) {
                $queue[] = $dep->depends_on_task_id;
            }
        }

        return false;
    }
}
