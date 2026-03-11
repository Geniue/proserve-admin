@php
    $fieldWrapperView = $getFieldWrapperView();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $options = $getOptions();
    $columns = $getGridColumns();
    $state = $getState();
    $id = $getId();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            open: false,
            search: '',
            options: @js($options),
            get filtered() {
                if (!this.search) return Object.entries(this.options);
                const s = this.search.toLowerCase();
                return Object.entries(this.options).filter(([key, label]) =>
                    key.includes(s) || label.toLowerCase().includes(s)
                );
            },
            select(key) {
                this.state = key;
                this.open = false;
                this.search = '';
            },
            toggle() {
                if ({{ $isDisabled ? 'true' : 'false' }}) return;
                this.open = !this.open;
                if (this.open) {
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                }
            },
            close() {
                this.open = false;
                this.search = '';
            }
        }"
        x-on:click.outside="close()"
        x-on:keydown.escape.window="close()"
        style="position: relative;"
    >
        {{-- Trigger button --}}
        <button
            type="button"
            x-on:click="toggle()"
            @if($isDisabled) disabled @endif
            style="display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; font-size: 14px; cursor: {{ $isDisabled ? 'not-allowed' : 'pointer' }}; {{ $isDisabled ? 'opacity: 0.7;' : '' }}"
        >
            <span style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                <template x-if="state">
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; background: #fef3c7;">
                            <iconify-icon
                                :icon="'heroicons:' + state"
                                width="18" height="18"
                                style="color: #d97706;"
                            ></iconify-icon>
                        </span>
                        <span style="color: #111; font-weight: 500;" x-text="options[state] || state"></span>
                    </span>
                </template>
                <template x-if="!state">
                    <span style="color: #9ca3af;">Select an icon...</span>
                </template>
            </span>
            <iconify-icon
                icon="heroicons:chevron-down"
                width="16" height="16"
                style="flex-shrink: 0; color: #9ca3af;"
                :style="open ? 'transform: rotate(180deg)' : ''"
            ></iconify-icon>
        </button>

        {{-- Picker Panel (floating box above the trigger) --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak
            style="position: absolute; z-index: 50; bottom: 100%; left: 0; right: 0; margin-bottom: 8px; min-width: 320px; max-width: 100%; background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 4px 10px -5px rgba(0,0,0,0.1);"
        >
            {{-- Search --}}
            <div style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb;">
                <div style="position: relative;">
                    <iconify-icon
                        icon="heroicons:magnifying-glass"
                        width="14" height="14"
                        style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none;"
                    ></iconify-icon>
                    <input
                        x-ref="searchInput"
                        x-model="search"
                        type="text"
                        placeholder="Search icons..."
                        style="width: 100%; padding: 6px 10px 6px 32px; font-size: 13px; border: 1px solid #d1d5db; border-radius: 6px; background: #f9fafb; outline: none; color: #111;"
                    />
                </div>
            </div>

            {{-- Icons Grid --}}
            <div style="padding: 8px; max-height: 240px; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: repeat({{ $columns }}, 1fr); gap: 2px;">
                    <template x-for="([key, label]) in filtered" :key="key">
                        <button
                            type="button"
                            x-on:click="select(key)"
                            :title="label"
                            style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; border: none; cursor: pointer; margin: auto; transition: background 0.15s;"
                            :style="state === key
                                ? 'background: #fef3c7; box-shadow: 0 0 0 2px #f59e0b;'
                                : 'background: transparent;'"
                            x-on:mouseenter="$el.style.background = state === key ? '#fef3c7' : '#f3f4f6'"
                            x-on:mouseleave="$el.style.background = state === key ? '#fef3c7' : 'transparent'"
                        >
                            <iconify-icon
                                :icon="'heroicons:' + key"
                                width="20" height="20"
                                style="color: #374151;"
                            ></iconify-icon>
                        </button>
                    </template>
                </div>

                {{-- No results --}}
                <template x-if="filtered.length === 0">
                    <p style="text-align: center; font-size: 13px; color: #9ca3af; padding: 16px 0;">No icons found</p>
                </template>
            </div>

            {{-- Footer --}}
            <div style="padding: 6px 12px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #9ca3af;">
                <span x-text="filtered.length + ' icons'"></span>
                <button type="button" x-on:click="state = null; close()" style="color: #ef4444; background: none; border: none; cursor: pointer; font-size: 11px;">Clear</button>
            </div>
        </div>
    </div>
</x-dynamic-component>
