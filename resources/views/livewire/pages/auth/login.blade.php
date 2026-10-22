<?php

use App\Enums\UserRole;
use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;
    public bool $showPassword = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = Auth::user();

        if ($user && $user->role === UserRole::Admin) {
            $this->redirectIntended(default: route('admin.approval', absolute: false), navigate: true);
            return;
        }

        Session::forget('url.intended');
        $this->redirect(route('catalog', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full max-w-[420px] mx-auto flex flex-col items-center">
    <!-- Brand Logo -->
    <div class="mb-8 text-center">
        <a href="/" wire:navigate class="inline-block transition-transform duration-200 hover:scale-105">
            <img src="{{ asset('images/logo.png') }}" alt="GHOSTGEAR Rental" class="h-20 sm:h-24 mx-auto object-contain drop-shadow-[0_0_20px_rgba(0,255,102,0.15)]">
        </a>
    </div>

    <!-- Login Box -->
    <div class="w-full space-y-6">
        @if (session('status'))
            <div class="p-3 text-sm rounded-xl bg-emerald-950/70 text-emerald-400 border border-emerald-800/60 text-center">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit="login" class="space-y-5">
            <!-- Username Input -->
            <div class="space-y-1.5">
                <label for="username" class="block text-xs font-medium text-slate-300">
                    Username
                </label>
                <div class="relative">
                    <input
                        wire:model="form.username"
                        id="username"
                        type="text"
                        name="username"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Masukkan username Anda..."
                        class="w-full bg-[#131B23] border border-[#1D2A37] text-white placeholder-slate-500 text-sm rounded-xl px-4 py-3.5 focus:outline-none focus:border-[#00FF66] focus:ring-1 focus:ring-[#00FF66] transition"
                    >
                </div>
                @error('form.username')
                    <p class="text-xs text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Input -->
            <div class="space-y-1.5" x-data="{ show: false }">
                <label for="password" class="block text-xs font-medium text-slate-300">
                    Kata sandi
                </label>
                <div class="relative">
                    <input
                        wire:model="form.password"
                        id="password"
                        :type="show ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••••••"
                        class="w-full bg-[#131B23] border border-[#1D2A37] text-white placeholder-slate-500 text-sm rounded-xl px-4 py-3.5 pr-11 focus:outline-none focus:border-[#00FF66] focus:ring-1 focus:ring-[#00FF66] transition tracking-wider"
                    >
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200 focus:outline-none transition"
                        title="Tampilkan / Sembunyikan Sandi"
                    >
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                @error('form.password')
                    <p class="text-xs text-rose-400 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full relative h-[48px] bg-[#00FF66] hover:bg-[#00E65C] disabled:opacity-80 disabled:cursor-wait active:scale-[0.99] text-black font-bold text-sm rounded-xl transition duration-150 shadow-[0_4px_16px_rgba(0,255,102,0.2)] flex items-center justify-center mt-2 cursor-pointer overflow-hidden"
            >
                <span wire:loading.remove wire:target="login" class="inline-flex items-center justify-center font-bold">
                    Masuk
                </span>
                <span wire:loading.flex wire:target="login" class="items-center justify-center gap-2.5 font-bold">
                    <svg class="animate-spin h-5 w-5 text-black shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memproses...</span>
                </span>
            </button>
        </form>

        <!-- Footer Notice -->
        <p class="text-center text-xs text-slate-500 pt-2">
            Akun didaftarkan oleh admin GhostGear Rental.
        </p>
    </div>
</div>
