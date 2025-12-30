<x-filament-panels::page.simple>
    @if (filament()->hasLogin())
        <x-slot name="heading">
            {{ __('filament-panels::pages/auth/login.heading') }}
        </x-slot>

        {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.before') }}

        <x-filament-panels::form wire:submit="authenticate">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>

        {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.after') }}

        {{-- Registration Link --}}
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Belum punya akun guru?
                <a href="{{ route('register.guru') }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                    Daftar di sini
                </a>
            </p>
        </div>
    @endif
</x-filament-panels::page.simple>
