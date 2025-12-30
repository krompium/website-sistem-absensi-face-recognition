<div x-data="{
    current: 1,
    total: {{ $record->frames_used }},
    session: @js($record->session_id),
    loading: true,
    getUrl() {
        const pad = String(this.current).padStart(4, '0');
        return '/secure-image/frame/' + this.session + '/' + pad;
    }
}" x-init="$watch('current', () => loading = true)">
    <div class="mb-4">
        <p class="text-sm text-gray-600">Session: {{ $record->session_id }}</p>
        <p class="text-sm text-gray-600">Total: {{ $record->frames_used }} frames</p>
    </div>

    <div class="relative bg-gray-900 rounded-lg" style="height: 500px;">
        <img x-bind:src="getUrl()" x-on:load="loading = false"
            x-on:error="console.error('Load error frame:', current)"
            class="w-full h-full object-contain transition-opacity" x-bind:class="loading ? 'opacity-0' : 'opacity-100'">

        <div x-show="loading" class="absolute inset-0 flex items-center justify-center">
            <span class="text-white">Loading frame <span x-text="current"></span>...</span>
        </div>
    </div>

    <div class="mt-4 flex gap-2 justify-center">
        <button type="button" x-on:click.stop.prevent="current = Math.max(1, current - 1)"
            x-bind:disabled="current === 1" class="px-4 py-2 bg-primary-600 text-white rounded disabled:opacity-50">
            ← Previous
        </button>

        <span class="px-4 py-2 bg-gray-100 rounded">
            Frame <span x-text="current"></span> / <span x-text="total"></span>
        </span>

        <button type="button" x-on:click.stop.prevent="current = Math.min(total, current + 1)"
            x-bind:disabled="current === total" class="px-4 py-2 bg-primary-600 text-white rounded disabled:opacity-50">
            Next →
        </button>
    </div>
</div>
