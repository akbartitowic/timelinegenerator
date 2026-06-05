<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

class ExcelExportService
{
    private Spreadsheet $spreadsheet;
    private \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet;

    private const COL_TASK_NAME   = 1;
    private const COL_START_DATE  = 2;
    private const COL_END_DATE    = 3;
    private const COL_DURATION    = 4;
    private const COL_PROGRESS    = 5;
    private const COL_DONE        = 6;
    private const COL_OWNER       = 7;
    private const COL_NOTES       = 8;
    private const GANTT_START_COL = 9;

    public function __construct(private WorkingDaysService $workingDays) {}

    public function generate(Project $project): string
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet       = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle('Timeline');

        $tasks = Task::where('project_id', $project->id)
            ->whereNull('parent_id')
            ->with(['subtasks' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $allTasks = Task::where('project_id', $project->id)->get();

        // Compute overall progress
        $overallProgress = $allTasks->count() > 0
            ? (int) round($allTasks->avg('progress'))
            : 0;

        // Header section
        $this->buildHeader($project, $overallProgress);

        // Determine gantt date range
        $taskDates = $allTasks->filter(fn($t) => $t->start_date && $t->end_date);
        $ganttStart = $ganttEnd = null;
        if ($taskDates->isNotEmpty()) {
            $ganttStart = Carbon::parse($taskDates->min('start_date'))->startOfWeek(Carbon::SUNDAY);
            $ganttEnd   = Carbon::parse($taskDates->max('end_date'))->endOfWeek(Carbon::SATURDAY);
        }

        // Build gantt columns map
        $ganttCols = [];
        if ($ganttStart && $ganttEnd) {
            $cursor = $ganttStart->copy();
            $col    = self::GANTT_START_COL;
            while ($cursor->lte($ganttEnd)) {
                $ganttCols[$cursor->format('Y-m-d')] = $col;
                $col++;
                $cursor->addDay();
            }
        }

        // Column headers row 11 & 12
        $this->buildColumnHeaders($ganttCols, $ganttStart);

        // Task rows starting at row 13
        $dataRow = 13;
        foreach ($tasks as $task) {
            $this->writeTaskRow($task, $dataRow, false, $ganttCols);
            $dataRow++;
            foreach ($task->subtasks as $subtask) {
                $this->writeTaskRow($subtask, $dataRow, true, $ganttCols);
                $dataRow++;
            }
        }

        // Auto-size left columns
        for ($c = 1; $c <= 8; $c++) {
            $this->sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }
        $this->sheet->getColumnDimensionByColumn(self::COL_TASK_NAME)->setWidth(42);
        $this->sheet->getColumnDimensionByColumn(self::COL_NOTES)->setWidth(30);

        // Narrow gantt columns
        foreach ($ganttCols as $col) {
            $this->sheet->getColumnDimensionByColumn($col)->setWidth(3.5);
        }

        // Freeze panes at task name + gantt start
        $this->sheet->freezePane('B13');

        $path = storage_path('app/temp/' . Str::uuid() . '.xlsx');
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);

        (new Xlsx($this->spreadsheet))->save($path);
        return $path;
    }

    private function buildHeader(Project $project, int $progress): void
    {
        $s = $this->sheet;

        // Row 2: Timeline title
        $s->mergeCells([1, 2, 8, 2]);
        $s->setCellValue([1, 2], 'Timeline');
        $s->getStyle([1, 2, 8, 2])->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $s->getRowDimension(2)->setRowHeight(28);

        // Row 5: Date
        $s->setCellValue([1, 5], 'Date:');
        $s->setCellValue([2, 5], now()->format('m/d/Y'));
        $s->getStyle([1, 5])->getFont()->setBold(true);

        // Row 7: Project info
        $s->setCellValue([1, 7], 'Select Project Name');
        $s->setCellValue([2, 7], 'Start Date');
        $s->setCellValue([3, 7], 'Deadline');
        $s->setCellValue([4, 7], 'Duration');
        $s->setCellValue([5, 7], 'Overall Progress');
        $s->getStyle([1, 7, 5, 7])->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFBFDBFE']],
        ]);

        // Row 8: Project data
        $s->setCellValue([1, 8], $project->name);
        $s->setCellValue([2, 8], $project->start_date->format('m/d/Y'));
        $s->setCellValue([3, 8], $project->deadline->format('m/d/Y'));
        $totalDays = $this->workingDays->countWorkingDays($project->start_date, $project->deadline);
        $s->setCellValue([4, 8], $totalDays);
        $s->setCellValue([5, 8], $progress . '%');
    }

    private function buildColumnHeaders(array $ganttCols, ?Carbon $ganttStart): void
    {
        $s = $this->sheet;

        $headers = ['Task Name', 'Start Date', 'End Date', 'Duration', 'Progress %', 'Done', 'Task Owner', 'Task Notes'];
        foreach ($headers as $i => $h) {
            $col = $i + 1;
            $s->setCellValue([$col, 11], $h);
            $s->getStyle([$col, 11])->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }
        $s->getRowDimension(11)->setRowHeight(20);

        if (empty($ganttCols) || !$ganttStart) return;

        // Week headers on row 11, day numbers on row 12
        $cursor  = $ganttStart->copy();
        $weekNum = 1;
        while (true) {
            $weekStart = $cursor->copy();
            $weekEnd   = $cursor->copy()->endOfWeek(Carbon::SATURDAY);

            // Collect columns for this week that exist in ganttCols
            $weekColStart = $weekColEnd = null;
            $temp = $weekStart->copy();
            while ($temp->lte($weekEnd)) {
                $key = $temp->format('Y-m-d');
                if (isset($ganttCols[$key])) {
                    if ($weekColStart === null) $weekColStart = $ganttCols[$key];
                    $weekColEnd = $ganttCols[$key];
                }
                $temp->addDay();
            }

            if ($weekColStart !== null) {
                // Merge and write week header row 11
                if ($weekColStart !== $weekColEnd) {
                    $s->mergeCells([$weekColStart, 11, $weekColEnd, 11]);
                }
                $label = 'Week ' . $weekNum . ': ' . $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');
                $s->setCellValue([$weekColStart, 11], $label);
                $bgColor = ($weekNum % 2 === 1) ? 'FF1E3A5F' : 'FF2563EB';
                $s->getStyle([$weekColStart, 11, $weekColEnd, 11])->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 8],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $weekNum++;
            }

            // Day numbers row 12
            $temp = $weekStart->copy();
            while ($temp->lte($weekEnd)) {
                $key = $temp->format('Y-m-d');
                if (isset($ganttCols[$key])) {
                    $col = $ganttCols[$key];
                    $s->setCellValue([$col, 12], $temp->day);
                    $isWeekend = $temp->isWeekend();
                    $dayBg     = $isWeekend ? 'FFD1D5DB' : 'FFBFDBFE';
                    $s->getStyle([$col, 12])->applyFromArray([
                        'font'      => ['size' => 7, 'bold' => $isWeekend],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $dayBg]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
                $temp->addDay();
            }

            $cursor = $weekEnd->addDay();
            if (!isset($ganttCols[$cursor->format('Y-m-d')])) break;
        }

        $s->getRowDimension(12)->setRowHeight(16);
    }

    private function writeTaskRow(Task $task, int $row, bool $isSubtask, array $ganttCols): void
    {
        $s = $this->sheet;

        $indent   = $isSubtask ? '    ' : '';
        $bgColor  = $isSubtask ? 'FFF9FAFB' : 'FFF3F4F6';

        // Left side data
        $s->setCellValue([self::COL_TASK_NAME, $row], $indent . $task->name);
        $s->setCellValue([self::COL_START_DATE, $row], $task->start_date?->format('m/d/Y') ?? '');
        $s->setCellValue([self::COL_END_DATE, $row], $task->end_date?->format('m/d/Y') ?? '');
        $s->setCellValue([self::COL_DURATION, $row], $task->duration_days ?: '–');
        $s->setCellValue([self::COL_PROGRESS, $row], $task->progress . '%');
        $s->setCellValue([self::COL_DONE, $row], $task->status === 'completed' ? '✓' : '');
        $s->setCellValue([self::COL_OWNER, $row], $task->task_owner ?? '');
        $s->setCellValue([self::COL_NOTES, $row], $task->notes ?? '');

        // Row styling
        $s->getStyle([1, $row, 8, $row])->applyFromArray([
            'fill'   => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
            'border' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
        ]);
        if (!$isSubtask) {
            $s->getStyle([self::COL_TASK_NAME, $row])->getFont()->setBold(true);
        }
        $s->getRowDimension($row)->setRowHeight(18);

        // Gantt bars
        if (!$task->start_date || !$task->end_date || empty($ganttCols)) return;

        $taskHex    = ltrim($task->color, '#');
        $taskArgb   = 'FF' . strtoupper($taskHex);
        $taskStart  = Carbon::parse($task->start_date)->startOfDay();
        $taskEnd    = Carbon::parse($task->end_date)->startOfDay();

        foreach ($ganttCols as $dateStr => $col) {
            $date = Carbon::parse($dateStr);
            if ($date->between($taskStart, $taskEnd)) {
                if ($this->workingDays->isWorkingDay($date)) {
                    $s->getStyle([$col, $row])->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($taskArgb);
                } else {
                    // Weekend/holiday within task range — lighter shade
                    $s->getStyle([$col, $row])->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFD1D5DB');
                }
            }
        }
    }
}
