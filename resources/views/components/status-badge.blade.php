@php
$config = match($status) {
    'in_progress' => ['bg-blue-50 text-blue-700 ring-blue-200',    'In Progress'],
    'completed'   => ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'Completed'],
    'on_hold'     => ['bg-amber-50 text-amber-700 ring-amber-200',  'On Hold'],
    default       => ['bg-neutral-100 text-neutral-500 ring-neutral-200', 'Not Started'],
};
@endphp
<div x-data="{ open: false }" class="relative inline-block">
    <button type="button" @click="open = !open"
        class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-md ring-1 cursor-pointer select-none {{ $config[0] }}">
        {{ $config[1] }}
        <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" @click.outside="open = false" x-transition
        class="absolute left-0 top-full mt-1 z-20 bg-white rounded-lg shadow-lg border border-neutral-200 py-1 min-w-[130px]">
        @foreach(['not_started' => 'Not Started', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'on_hold' => 'On Hold'] as $val => $label)
        <button type="button"
            wire:click="updateStatus({{ $taskId }}, '{{ $val }}')"
            @click="open = false"
            class="w-full text-left px-3 py-1.5 text-xs hover:bg-neutral-50 transition-colors
                   {{ $status === $val ? 'font-semibold text-neutral-900' : 'text-neutral-600' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>
</div>
