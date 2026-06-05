<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ExcelExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectExportController extends Controller
{
    public function __construct(private ExcelExportService $excelService) {}

    public function export(Project $project): BinaryFileResponse
    {
        $path = $this->excelService->generate($project);
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $project->name) . '-timeline.xlsx';
        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }
}
