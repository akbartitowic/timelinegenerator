<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900">Hari Libur</h1>
            <p class="text-sm text-neutral-500 mt-0.5">Tanggal libur tidak bisa dipilih sebagai start/end date task</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Add form --}}
        <div class="bg-white rounded-xl border border-neutral-200 p-5 h-fit">
            <h2 class="text-sm font-semibold text-neutral-900 mb-4">Tambah Hari Libur</h2>
            <form wire:submit="add" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Tanggal</label>
                    <input wire:model="newDate" type="date"
                        class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('newDate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Nama (opsional)</label>
                    <input wire:model="newName" type="text" placeholder="e.g. Hari Raya Idul Fitri"
                        class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Tambah
                </button>
            </form>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-neutral-200 overflow-hidden">
            @if($holidays->isEmpty())
            <div class="text-center py-16 text-neutral-500 text-sm">Belum ada hari libur.</div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-neutral-50 border-b border-neutral-200">
                        <th class="text-left px-4 py-3 font-medium text-neutral-600">Tanggal</th>
                        <th class="text-left px-4 py-3 font-medium text-neutral-600">Nama</th>
                        <th class="text-left px-4 py-3 font-medium text-neutral-600">Hari</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach($holidays as $holiday)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-4 py-3 font-medium text-neutral-900">
                            {{ $holiday->date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-neutral-600">
                            {{ $holiday->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-neutral-500">
                            {{ $holiday->date->isoFormat('dddd') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="remove({{ $holiday->id }})"
                                wire:confirm="Hapus hari libur ini?"
                                class="text-neutral-400 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
