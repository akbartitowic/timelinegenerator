<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('User Management')]
class UsersPage extends Component
{
    public bool   $showModal    = false;
    public ?int   $editingId    = null;

    public string $name         = '';
    public string $email        = '';
    public string $password     = '';
    public bool   $isAdmin      = false;

    protected function rules(): array
    {
        $passwordRule = $this->editingId
            ? 'nullable|string|min:6'
            : 'required|string|min:6';

        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . ($this->editingId ?? 'NULL'),
            'password' => $passwordRule,
            'isAdmin'  => 'boolean',
        ];
    }

    public function openAdd(): void
    {
        $this->reset(['name', 'email', 'password', 'isAdmin', 'editingId']);
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingId = $userId;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->password  = '';
        $this->isAdmin   = (bool) $user->is_admin;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $data = [
                'name'     => $this->name,
                'email'    => $this->email,
                'is_admin' => $this->isAdmin,
            ];
            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }
            User::findOrFail($this->editingId)->update($data);
        } else {
            User::create([
                'name'     => $this->name,
                'email'    => $this->email,
                'password' => Hash::make($this->password),
                'is_admin' => $this->isAdmin,
            ]);
        }

        $this->showModal = false;
        $this->reset(['name', 'email', 'password', 'isAdmin', 'editingId']);
    }

    public function deleteUser(int $userId): void
    {
        if ($userId === auth()->id()) {
            return;
        }
        User::findOrFail($userId)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name', 'email', 'password', 'isAdmin', 'editingId']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.users-page', [
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
