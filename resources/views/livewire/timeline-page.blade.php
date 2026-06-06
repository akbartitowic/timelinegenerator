<div
    x-data="timelineApp(@js($holidaysJson))"
    x-init="init()"
    x-on:holiday-updated.window="reloadHolidays()"
>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-1.5 text-sm text-neutral-400 mb-5">
        <a href="{{ route('projects.index') }}" class="hover:text-neutral-700 transition-colors">Projects</a>
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-neutral-700 font-medium">{{ $project->name }}</span>
    </div>

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-neutral-900 tracking-tight">{{ $project->name }}</h1>
            <div class="flex items-center gap-3 mt-1.5">
                <span class="inline-flex items-center gap-1 text-xs text-neutral-500">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $project->start_date->format('d M Y') }} – {{ $project->deadline->format('d M Y') }}
                </span>
                <span class="text-neutral-300">·</span>
                <span class="text-xs text-neutral-500">{{ $tasks->count() }} tasks</span>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button wire:click="openAddTask"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Task
            </button>
            <a href="{{ route('projects.export', $project) }}"
                class="inline-flex items-center gap-2 bg-white hover:bg-neutral-50 text-neutral-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors border border-neutral-200 shadow-sm">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    {{-- Task table --}}
    <div class="bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[960px]">
                <thead>
                    <tr class="border-b border-neutral-200 bg-neutral-50">
                        <th class="w-9 px-3 py-3"></th>
                        <th class="text-left px-3 py-3 font-medium text-neutral-500 text-xs uppercase tracking-wider min-w-[260px]">Task Name</th>
                        <th class="text-left px-3 py-3 font-medium text-neutral-500 text-xs uppercase tracking-wider w-28">Start</th>
                        <th class="text-left px-3 py-3 font-medium text-neutral-500 text-xs uppercase tracking-wider w-28">End</th>
                        <th class="text-center px-3 py-3 font-medium text-neutral-500 text-xs uppercase tracking-wider w-20">Days</th>
                        <th class="text-left px-3 py-3 font-medium text-neutral-500 text-xs uppercase tracking-wider w-36">Progress</th>
                        <th class="text-left px-3 py-3 font-medium text-neutral-500 text-xs uppercase tracking-wider w-30">Status</th>
                        <th class="text-left px-3 py-3 font-medium text-neutral-500 text-xs uppercase tracking-wider w-30">Owner</th>
                        <th class="w-24 px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody id="tasks-sortable">
                    @forelse($tasks as $task)

                    {{-- Parent task row --}}
                    <tr class="border-b border-neutral-100 hover:bg-neutral-50/80 transition-colors group" data-task-id="{{ $task->id }}">
                        <td class="px-3 py-3 drag-handle cursor-grab text-neutral-300 hover:text-neutral-400 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                            </svg>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color:{{ $task->color }}"></div>
                                <span class="font-semibold text-neutral-900 leading-tight">{{ $task->name }}</span>
                                @if($task->dependsOn)
                                <span class="inline-flex items-center gap-1 text-xs text-neutral-400 bg-neutral-100 px-1.5 py-0.5 rounded-md">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    {{ $task->dependsOn->name }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-3 text-xs text-neutral-500 tabular-nums">{{ $task->start_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-3 py-3 text-xs text-neutral-500 tabular-nums">{{ $task->end_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-3 py-3 text-center text-xs font-medium text-neutral-600">{{ $task->duration_days ?: '—' }}</td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-neutral-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300"
                                        style="width:{{ $task->progress }}%; background-color:{{ $task->color }}"></div>
                                </div>
                                <span class="text-xs tabular-nums text-neutral-500 w-7 text-right">{{ $task->progress }}%</span>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            @include('components.status-badge', ['status' => $task->status, 'taskId' => $task->id])
                        </td>
                        <td class="px-3 py-3 text-xs text-neutral-500">{{ $task->task_owner ?? '—' }}</td>
                        <td class="px-3 py-3">
                            <div class="flex items-center justify-end gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="openAddSubtask({{ $task->id }})" title="Add subtask"
                                    class="p-1.5 text-neutral-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                                <button wire:click="openEditTask({{ $task->id }})" title="Edit"
                                    class="p-1.5 text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 rounded-md transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="deleteTask({{ $task->id }})" wire:confirm="Hapus task ini beserta semua subtask-nya?" title="Delete"
                                    class="p-1.5 text-neutral-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Subtask rows --}}
                    @foreach($task->subtasks as $subtask)
                    <tr class="border-b border-neutral-100 hover:bg-blue-50/20 transition-colors group bg-white" data-subtask-id="{{ $subtask->id }}">
                        <td class="px-3 py-2.5"></td>
                        <td class="px-3 py-2.5 pl-9">
                            <div class="flex items-center gap-2">
                                <div class="w-0.5 h-4 bg-neutral-200 rounded-full flex-shrink-0 -ml-3.5 mr-1"></div>
                                <div class="w-2 h-2 rounded-full flex-shrink-0 border-2" style="border-color:{{ $subtask->color }}"></div>
                                <span class="text-neutral-700 text-sm">{{ $subtask->name }}</span>
                                @if($subtask->dependsOn)
                                <span class="inline-flex items-center gap-1 text-xs text-neutral-400 bg-neutral-100 px-1.5 py-0.5 rounded-md">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    {{ $subtask->dependsOn->name }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-2.5 text-xs text-neutral-400 tabular-nums">{{ $subtask->start_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-xs text-neutral-400 tabular-nums">{{ $subtask->end_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-center text-xs text-neutral-400">{{ $subtask->duration_days ?: '—' }}</td>
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1 bg-neutral-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300"
                                        style="width:{{ $subtask->progress }}%; background-color:{{ $subtask->color }}"></div>
                                </div>
                                <span class="text-xs tabular-nums text-neutral-400 w-7 text-right">{{ $subtask->progress }}%</span>
                            </div>
                        </td>
                        <td class="px-3 py-2.5">
                            @include('components.status-badge', ['status' => $subtask->status, 'taskId' => $subtask->id])
                        </td>
                        <td class="px-3 py-2.5 text-xs text-neutral-400">{{ $subtask->task_owner ?? '—' }}</td>
                        <td class="px-3 py-2.5">
                            <div class="flex items-center justify-end gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="moveTaskUp({{ $subtask->id }})" title="Move up"
                                    class="p-1.5 text-neutral-300 hover:text-neutral-600 hover:bg-neutral-100 rounded-md transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button wire:click="moveTaskDown({{ $subtask->id }})" title="Move down"
                                    class="p-1.5 text-neutral-300 hover:text-neutral-600 hover:bg-neutral-100 rounded-md transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button wire:click="openEditTask({{ $subtask->id }})" title="Edit"
                                    class="p-1.5 text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 rounded-md transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="deleteTask({{ $subtask->id }})" wire:confirm="Hapus subtask ini?" title="Delete"
                                    class="p-1.5 text-neutral-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @empty
                    <tr>
                        <td colspan="9" class="py-20 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-neutral-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm text-neutral-400">Belum ada task. Klik <strong class="text-neutral-600">Add Task</strong> untuk mulai.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── Task Modal ─────────────────────────────────────────── --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[92vh] flex flex-col">

            {{-- ── Modal header ── --}}
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-neutral-100">
                <div class="flex items-center gap-3">
                    @if($modalStep === 'count')
                        <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-neutral-900">Atur Revisi</h2>
                    @elseif($modalStep === 'ask')
                        <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-neutral-900">
                            {{ $parentTaskId ? 'Add Subtask' : 'New Task' }}
                        </h2>
                    @else
                        <div class="w-3 h-3 rounded-full ring-2 ring-offset-1 flex-shrink-0"
                            style="background-color:{{ $taskColor }}; ring-color:{{ $taskColor }}"></div>
                        <div>
                            <h2 class="text-base font-semibold text-neutral-900">
                                @if($editingTaskId) Edit Task
                                @elseif($parentTaskId) Add Subtask
                                @else New Task
                                @endif
                            </h2>
                            @if($editingTaskId)
                            <p class="text-xs text-neutral-400 mt-0.5">ID #{{ $editingTaskId }}</p>
                            @elseif($withRevisions)
                            <p class="text-xs text-amber-600 mt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                {{ $revisionCount }} revisi · {{ $revisionCount * 2 }} subtask otomatis
                            </p>
                            @endif
                        </div>
                    @endif
                </div>
                <button wire:click="closeModal"
                    class="p-1.5 text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- ── Step: Ask ── --}}
            @if($modalStep === 'ask')
            <div class="flex flex-col items-center justify-center px-8 py-10 text-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-neutral-900 mb-1.5">Task ini memiliki revisi?</h3>
                    <p class="text-sm text-neutral-500 leading-relaxed">Jika ya, subtask <span class="font-medium text-amber-600">Revisi</span> dan <span class="font-medium text-emerald-600">Review</span><br>akan dibuat otomatis untuk setiap putaran.</p>
                </div>
                <div class="flex gap-3 w-full">
                    <button type="button" wire:click="setRevisionAnswer(false)"
                        class="flex-1 py-3 rounded-xl border-2 border-neutral-200 text-sm font-medium text-neutral-700 hover:border-neutral-300 hover:bg-neutral-50 transition-all">
                        Tidak
                    </button>
                    <button type="button" wire:click="setRevisionAnswer(true)"
                        class="flex-1 py-3 rounded-xl border-2 border-blue-500 bg-blue-500 text-sm font-medium text-white hover:bg-blue-600 transition-all flex items-center justify-center gap-2">
                        Ya, ada revisi
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ── Step: Count ── --}}
            @elseif($modalStep === 'count')
            <div class="flex flex-col flex-1 min-h-0">
                <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">

                    {{-- Number picker --}}
                    <div>
                        <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-3">Berapa kali revisi?</p>
                        <div class="grid grid-cols-5 gap-2">
                            @for($i = 1; $i <= 10; $i++)
                            <button type="button" wire:click="selectRevisionCount({{ $i }})"
                                class="py-3 rounded-xl text-sm font-bold transition-all
                                    {{ $revisionCount === $i
                                        ? 'bg-blue-500 text-white shadow-md shadow-blue-200 scale-105'
                                        : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }}">
                                {{ $i }}
                            </button>
                            @endfor
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-4">
                        <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-3">Preview subtask yang akan dibuat</p>
                        <div class="space-y-2 @if($revisionCount > 5) max-h-40 overflow-y-auto pr-1 @endif">
                            @for($i = 1; $i <= $revisionCount; $i++)
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-neutral-400 w-4 shrink-0">{{ $i }}.</span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-100 text-amber-700 text-xs font-medium">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Revisi {{ $i }}
                                </span>
                                <svg class="w-3.5 h-3.5 text-neutral-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-medium">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Review {{ $i }}
                                </span>
                            </div>
                            @endfor
                        </div>
                        <p class="text-xs text-neutral-400 mt-3 pt-3 border-t border-neutral-200">
                            Total <span class="font-semibold text-neutral-700">{{ $revisionCount * 2 }}</span> subtask akan dibuat otomatis
                        </p>
                    </div>

                </div>

                <div class="flex items-center justify-between px-6 py-4 border-t border-neutral-100 bg-neutral-50/50 rounded-b-2xl">
                    <button type="button" wire:click="backToAsk()"
                        class="flex items-center gap-1.5 text-sm text-neutral-500 hover:text-neutral-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Kembali
                    </button>
                    <button type="button" wire:click="goToFormStep()"
                        class="px-5 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-sm flex items-center gap-2">
                        Lanjut isi detail
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ── Step: Form ── --}}
            @else
            <form wire:submit="saveTask" class="flex flex-col flex-1 min-h-0">
                <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">

                    {{-- Revision badge (if active) --}}
                    @if($withRevisions && !$editingTaskId)
                    <div class="flex items-center gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <p class="text-xs text-amber-700">
                            Setelah disimpan, <span class="font-semibold">{{ $revisionCount * 2 }} subtask</span> akan dibuat otomatis
                            ({{ $revisionCount }}× Revisi + {{ $revisionCount }}× Review)
                        </p>
                        <button type="button" wire:click="backToAsk()" class="ml-auto text-xs text-amber-600 hover:text-amber-800 underline shrink-0">ubah</button>
                    </div>
                    @endif

                    {{-- Task Name --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1.5">
                            Task Name <span class="text-red-400 normal-case font-normal">*</span>
                        </label>
                        <input wire:model="taskName" type="text" placeholder="e.g. UI Design"
                            class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm text-neutral-900 placeholder-neutral-400
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-colors">
                        @error('taskName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ── Dependency ── --}}
                    <div>
                        <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span>Dependency</span>
                            <span class="flex-1 h-px bg-neutral-100"></span>
                        </p>
                        <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-3.5 space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-neutral-600 mb-1.5">Mulai setelah task</label>
                                <select wire:model.live="taskDependsOn"
                                    class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <option value="">— Tidak bergantung pada task lain —</option>
                                    @foreach($allTasksForDropdown as $t)
                                        @if(!$editingTaskId || $t->id !== $editingTaskId)
                                        <option value="{{ $t->id }}">{{ $t->name }}{{ $t->end_date ? ' (selesai '.$t->end_date->format('d M').')' : '' }}</option>
                                        @endif
                                        @foreach($t->subtasks as $sub)
                                            @if(!$editingTaskId || $sub->id !== $editingTaskId)
                                            <option value="{{ $sub->id }}">↳ {{ $sub->name }}{{ $sub->end_date ? ' (selesai '.$sub->end_date->format('d M').')' : '' }}</option>
                                            @endif
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('taskDependsOn') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            @if($taskDependsOn)
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-neutral-600 mb-1.5">Jeda setelah selesai</label>
                                    <div class="relative">
                                        <input wire:model.live.debounce.500ms="taskOffsetDays" type="number" min="1"
                                            class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 pr-20 text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-neutral-400 pointer-events-none">hari kerja</span>
                                    </div>
                                </div>
                                @if($taskStartDate)
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-neutral-600 mb-1.5">Start date otomatis</p>
                                    <div class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700">
                                        {{ \Carbon\Carbon::parse($taskStartDate)->isoFormat('D MMM Y') }}
                                    </div>
                                </div>
                                @endif
                            </div>
                            @else
                            <p class="text-xs text-neutral-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Pilih task di atas untuk mengatur dependency. Start date akan terisi otomatis.
                            </p>
                            @endif
                        </div>
                    </div>

                    {{-- ── Waktu ── --}}
                    <div>
                        <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span>Waktu</span>
                            <span class="flex-1 h-px bg-neutral-100"></span>
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            {{-- Start Date --}}
                            <div>
                                <label class="block text-xs font-medium text-neutral-600 mb-1.5">
                                    Start Date
                                    @if($taskDependsOn)
                                    <span class="text-blue-500 font-normal ml-1">· otomatis dari dependency</span>
                                    @endif
                                </label>
                                <input id="fp-start-date" type="text"
                                    placeholder="{{ $taskDependsOn ? 'Terisi otomatis' : 'Pilih tanggal' }}"
                                    {{ $taskDependsOn ? 'readonly' : '' }}
                                    class="w-full rounded-lg border px-3 py-2.5 text-sm transition-colors
                                           {{ $taskDependsOn
                                               ? 'border-blue-200 bg-blue-50 text-blue-700 cursor-default focus:outline-none'
                                               : 'border-neutral-200 bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white' }}"
                                    autocomplete="off">
                                @error('taskStartDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- End Date --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-medium text-neutral-600">End Date</label>
                                    <div class="flex items-center bg-neutral-100 rounded-md p-0.5 text-xs">
                                        <button type="button" wire:click="setEndDateMode('date')"
                                            class="px-2 py-0.5 rounded transition-all {{ $taskEndDateMode === 'date' ? 'bg-white text-neutral-800 shadow-sm font-medium' : 'text-neutral-500 hover:text-neutral-700' }}">
                                            Tanggal
                                        </button>
                                        <button type="button" wire:click="setEndDateMode('duration')"
                                            class="px-2 py-0.5 rounded transition-all {{ $taskEndDateMode === 'duration' ? 'bg-white text-neutral-800 shadow-sm font-medium' : 'text-neutral-500 hover:text-neutral-700' }}">
                                            Durasi
                                        </button>
                                    </div>
                                </div>

                                @if($taskEndDateMode === 'date')
                                    <input id="fp-end-date" type="text" placeholder="Pilih tanggal"
                                        class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-colors"
                                        autocomplete="off">
                                    @error('taskEndDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                @else
                                    <div class="relative">
                                        <input wire:model.live.debounce.400ms="taskDurationInput"
                                            type="number" min="1" placeholder="10"
                                            class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5 pr-20 text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-colors">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-neutral-400 pointer-events-none">hari kerja</span>
                                    </div>
                                    @if($taskStartDate && $taskEndDate)
                                    <p class="text-xs text-blue-600 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                        Selesai {{ \Carbon\Carbon::parse($taskEndDate)->isoFormat('dddd, D MMM Y') }}
                                    </p>
                                    @endif
                                    @error('taskDurationInput') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Detail ── --}}
                    <div>
                        <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span>Detail</span>
                            <span class="flex-1 h-px bg-neutral-100"></span>
                        </p>
                        <div class="space-y-3">
                            {{-- Status + Progress --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-neutral-600 mb-1.5">Status</label>
                                    <select wire:model="taskStatus"
                                        class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-colors">
                                        <option value="not_started">Not Started</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="on_hold">On Hold</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-neutral-600 mb-1.5">
                                        Progress
                                        <span class="font-semibold text-neutral-800 ml-1">{{ $taskProgress }}%</span>
                                    </label>
                                    <input wire:model.live="taskProgress" type="range" min="0" max="100" step="5"
                                        class="w-full h-2 rounded-full accent-blue-600 cursor-pointer mt-1.5">
                                </div>
                            </div>

                            {{-- Owner + Color --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-neutral-600 mb-1.5">Task Owner</label>
                                    <input wire:model="taskOwner" type="text" placeholder="Nama PIC"
                                        class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-colors">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-neutral-600 mb-1.5">Warna</label>
                                    <div class="flex items-center gap-2">
                                        <div class="relative">
                                            <input wire:model.live="taskColor" type="color"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer rounded-lg">
                                            <div class="w-10 h-10 rounded-lg border border-neutral-200 shadow-sm flex-shrink-0 pointer-events-none"
                                                style="background-color:{{ $taskColor }}"></div>
                                        </div>
                                        <div>
                                            <p class="text-xs font-mono text-neutral-700 font-medium">{{ strtoupper($taskColor) }}</p>
                                            <p class="text-xs text-neutral-400">klik untuk ubah</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label class="block text-xs font-medium text-neutral-600 mb-1.5">Notes</label>
                                <textarea wire:model="taskNotes" rows="2" placeholder="Catatan tambahan..."
                                    class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm resize-none
                                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-colors"></textarea>
                            </div>
                        </div>
                    </div>

                </div>{{-- end scrollable body --}}

                {{-- Modal footer --}}
                <div class="flex items-center justify-between px-6 py-4 border-t border-neutral-100 bg-neutral-50/50 rounded-b-2xl">
                    <p class="text-xs text-neutral-400">
                        @if($taskStartDate && $taskEndDate)
                        <span class="font-medium text-neutral-600">{{ $taskDurationInput > 0 && $taskEndDateMode === 'duration' ? $taskDurationInput : ((\Carbon\Carbon::parse($taskStartDate)->diffInDays(\Carbon\Carbon::parse($taskEndDate))) + 1) }}</span> hari kalender
                        @endif
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-sm text-neutral-600 hover:text-neutral-900 rounded-lg border border-neutral-200 hover:bg-neutral-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-sm">
                            {{ $editingTaskId ? 'Simpan Perubahan' : 'Buat Task' }}
                        </button>
                    </div>
                </div>

            </form>
            @endif
        </div>
    </div>
    @endif
</div>
