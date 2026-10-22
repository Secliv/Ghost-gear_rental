<?php

use App\Enums\UserRole;
use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-slate-900 border-b border-slate-800 text-white shadow-lg sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center gap-3">
                    <a href="{{ route('catalog') }}" wire:navigate class="flex items-center gap-2 group">
                        <span class="text-2xl">👻</span>
                        <div class="flex flex-col">
                            <span class="font-extrabold text-lg tracking-wider text-emerald-400 group-hover:text-emerald-300 transition">GHOSTGEAR</span>
                            <span class="text-[10px] tracking-widest text-slate-400 uppercase -mt-1">Paranormal Rental</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-4 sm:-my-px sm:flex items-center">
                    <a href="{{ route('catalog') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('catalog*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}"
                       wire:navigate>
                        🔭 Katalog Peralatan
                    </a>

                    <a href="{{ route('member.loans') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('member.loans*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}"
                       wire:navigate>
                        📋 Pinjaman Saya
                    </a>

                    @if (auth()->user()?->role === UserRole::Admin)
                        <a href="/admin"
                           class="px-3 py-2 rounded-md text-sm font-medium bg-amber-600/30 text-amber-300 border border-amber-500/40 hover:bg-amber-600 hover:text-white transition">
                            ⚡ Panel Admin
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-slate-700 text-sm leading-4 font-medium rounded-lg text-slate-200 bg-slate-800 hover:bg-slate-700 hover:text-white focus:outline-none transition ease-in-out duration-150 gap-2">
                            <div class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr(auth()->user()->profile?->full_name ?? auth()->user()->username, 0, 1)) }}
                            </div>
                            <div class="text-left">
                                <div class="text-xs font-semibold leading-tight">{{ auth()->user()->profile?->full_name ?? auth()->user()->username }}</div>
                                <div class="text-[10px] text-slate-400 leading-none">&#64;{{ auth()->user()->username }}</div>
                            </div>
                            <svg class="fill-current h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('member.profile')" wire:navigate>
                            👤 Profil Saya
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link class="text-rose-600 dark:text-rose-400">
                                🚪 Log Out
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-slate-950 border-t border-slate-800">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('catalog') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white" wire:navigate>
                🔭 Katalog Peralatan
            </a>
            <a href="{{ route('member.loans') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white" wire:navigate>
                📋 Pinjaman Saya
            </a>
            @if (auth()->user()?->role === UserRole::Admin)
                <a href="/admin" class="block px-3 py-2 rounded-md text-sm font-medium text-amber-400 hover:bg-slate-800">
                    ⚡ Panel Admin
                </a>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-slate-800 px-4">
            <div class="font-medium text-base text-slate-200">{{ auth()->user()->profile?->full_name ?? auth()->user()->username }}</div>
            <div class="font-medium text-xs text-slate-400">&#64;{{ auth()->user()->username }}</div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('member.profile') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:bg-slate-800" wire:navigate>
                    👤 Profil Saya
                </a>
                <button wire:click="logout" class="w-full text-start block px-3 py-2 rounded-md text-sm font-medium text-rose-400 hover:bg-slate-800">
                    🚪 Log Out
                </button>
            </div>
        </div>
    </div>
</nav>
