<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-lg font-semibold text-neutral-900">User Management</h1>
            <p class="text-sm text-neutral-500 mt-0.5">{{ $users->count() }} user terdaftar</p>
        </div>
        <button wire:click="openAdd"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah User
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nama</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-neutral-400 uppercase tracking-wider">Email</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-neutral-400 uppercase tracking-wider">Role</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-neutral-400 uppercase tracking-wider">Dibuat</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-50">
                @forelse($users as $user)
                <tr class="group hover:bg-neutral-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-neutral-200 flex items-center justify-center shrink-0">
                                <span class="text-xs font-semibold text-neutral-600">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-neutral-900">{{ $user->name }}</p>
                                @if($user->id === auth()->id())
                                <span class="text-[11px] text-blue-500 font-medium">Anda</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-neutral-600">{{ $user->email }}</td>
                    <td class="px-5 py-3.5">
                        @if($user->is_admin)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-purple-50 text-purple-700 ring-1 ring-purple-200">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Admin
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-neutral-100 text-neutral-600 ring-1 ring-neutral-200">
                            Member
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-neutral-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button wire:click="openEdit({{ $user->id }})"
                                class="p-1.5 rounded-lg text-neutral-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @if($user->id !== auth()->id())
                            <button wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="Hapus user {{ $user->name }}?"
                                class="p-1.5 rounded-lg text-neutral-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                title="Hapus">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-sm text-neutral-400">Belum ada user.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl flex flex-col"
             x-data x-trap.noscroll="true">

            {{-- Modal header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100">
                <h2 class="text-base font-semibold text-neutral-900">
                    {{ $editingId ? 'Edit User' : 'Tambah User' }}
                </h2>
                <button wire:click="closeModal"
                    class="p-1.5 rounded-lg text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal body --}}
            <form wire:submit="save" class="px-6 py-5 space-y-4">

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1.5">Nama <span class="text-red-400">*</span></label>
                    <input wire:model="name" type="text" placeholder="John Doe"
                        class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-colors">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1.5">Email <span class="text-red-400">*</span></label>
                    <input wire:model="email" type="email" placeholder="john@example.com"
                        class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-colors">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1.5">
                        Password
                        @if($editingId)
                        <span class="text-neutral-400 font-normal">— kosongkan jika tidak ingin mengubah</span>
                        @else
                        <span class="text-red-400">*</span>
                        @endif
                    </label>
                    <input wire:model="password" type="password" placeholder="Min. 6 karakter"
                        class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-colors">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Role --}}
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1.5">Role</label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input wire:model="isAdmin" type="checkbox"
                            class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <div>
                            <span class="text-sm text-neutral-800 font-medium">Admin</span>
                            <p class="text-xs text-neutral-400">Dapat mengakses Holidays dan User Management</p>
                        </div>
                    </label>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-neutral-600 hover:bg-neutral-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        {{ $editingId ? 'Simpan' : 'Tambah' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
