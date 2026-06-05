<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;

class WorkingDaysService
{
    private array $holidayMap = [];
    private bool  $loaded     = false;

    private function load(): void
    {
        if ($this->loaded) return;
        $this->holidayMap = Holiday::pluck('date')
            ->mapWithKeys(fn($d) => [$d->format('Y-m-d') => true])
            ->all();
        $this->loaded = true;
    }

    public function reload(): void
    {
        $this->loaded = false;
        $this->load();
    }

    public function isWorkingDay(Carbon $date): bool
    {
        $this->load();
        return !$date->isWeekend()
            && !isset($this->holidayMap[$date->format('Y-m-d')]);
    }

    public function addWorkingDays(Carbon $start, int $days): Carbon
    {
        $date  = $start->copy();
        $added = 0;
        while ($added < $days) {
            $date->addDay();
            if ($this->isWorkingDay($date)) $added++;
        }
        return $date;
    }

    public function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $count  = 0;
        $cursor = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();
        while ($cursor->lte($endDay)) {
            if ($this->isWorkingDay($cursor)) $count++;
            $cursor->addDay();
        }
        return $count;
    }

    public function nextWorkingDay(Carbon $after): Carbon
    {
        return $this->addWorkingDays($after, 1);
    }

    public function getHolidayDatesForFrontend(): array
    {
        $this->load();
        return array_keys($this->holidayMap);
    }
}
