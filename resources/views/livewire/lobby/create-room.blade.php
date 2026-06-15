<div class="flex items-center justify-center min-h-screen">
    <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-8 w-full max-w-md">
        <h2 class="font-cinzel text-xl text-white mb-6 text-center">{{ __('ui.create_room') }}</h2>
        <form wire:submit="submit">
            <div class="mb-4">
                <label for="nickname" class="block text-sm text-gray-400 mb-1">{{ __('ui.nickname') }}</label>
                <input
                    type="text"
                    id="nickname"
                    wire:model="nickname"
                    maxlength="30"
                    required
                    class="w-full bg-gray-800 border border-gray-600 text-white px-3 py-2 rounded focus:outline-none focus:border-amber-500"
                />
                @error('nickname')
                    <span class="text-red-400 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-500 text-white font-cinzel py-2 rounded transition-colors">
                {{ __('ui.create_room') }}
            </button>
        </form>
    </div>
</div>

@script
<script>
    $wire.on('redirect', ({url}) => {
        window.location.href = url;
    });
</script>
@endscript
