<div>
    <form wire:submit="submit">
        <div>
            <label for="nickname">{{ __('ui.nickname') }}</label>
            <input
                type="text"
                id="nickname"
                wire:model="nickname"
                maxlength="30"
                required
            />
            @error('nickname')
                <span class="text-accent-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit">{{ __('ui.create_room') }}</button>
    </form>
</div>
