@if (isset($items) && method_exists($items, 'links'))
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="text-muted small">{{ __('Showing') }} {{ $items->firstItem() ?? 0 }} {{ __('to') }} {{ $items->lastItem() ?? 0 }} {{ __('of') }} {{ $items->total() ?? 0 }} {{ __('results') }}</span>
        {{ $items->links() }}
    </div>
@endif
