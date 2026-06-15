<x-app-layout>
    <div class="flex flex-col items-center justify-center min-h-screen">
        <h1 class="font-cinzel text-4xl text-text-primary mb-8">{{ config('app.name') }}</h1>

        <div class="flex gap-4 mb-8">
            <a
                href="{{ route('locale', 'en') }}"
                class="{{ app()->locale === 'en' ? 'text-accent-warm' : 'text-text-secondary' }}"
            >EN</a>
            <span class="text-text-secondary">|</span>
            <a
                href="{{ route('locale', 'fr') }}"
                class="{{ app()->locale === 'fr' ? 'text-accent-warm' : 'text-text-secondary' }}"
            >FR</a>
        </div>

        <div class="flex flex-col gap-4">
            <a
                href="{{ route('create') }}"
                class="bg-elevated hover:bg-accent-warm text-text-primary font-cinzel px-8 py-4 rounded text-center transition-colors"
            >{{ __('ui.create_room') }}</a>

            <a
                href="{{ route('join') }}"
                class="bg-elevated hover:bg-accent-warm text-text-primary font-cinzel px-8 py-4 rounded text-center transition-colors"
            >{{ __('ui.join_room') }}</a>
        </div>
    </div>
</x-app-layout>
