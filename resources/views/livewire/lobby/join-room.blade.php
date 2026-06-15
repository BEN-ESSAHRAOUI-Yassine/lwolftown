<div>
    <form wire:submit="submit">
        <div>
            <label for="code">{{ __('ui.room_code') }}</label>
            <input
                type="text"
                id="code"
                wire:model="code"
                maxlength="6"
                required
            />
            @error('code')
                <span class="text-accent-danger">{{ $message }}</span>
            @enderror
        </div>

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

        <button type="submit">{{ __('ui.join_room') }}</button>
    </form>
</div>
