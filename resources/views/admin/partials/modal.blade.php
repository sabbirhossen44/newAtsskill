{{-- Generic delete-confirm modal; admin.js fills #deleteForm action from [data-delete-url]. --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="deleteModalLabel">{{ __('Delete Confirmation') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body">{{ __('Are you sure you want to delete this item? This action cannot be undone.') }}</div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <form id="deleteForm" method="POST" action=""><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button type="submit" class="btn btn-danger">{{ __('Delete') }}</button></form>
        </div>
    </div></div>
</div>
{{-- Generic dynamic modal (AJAX content target, mirrors old #exampleModalCenter). --}}
<div class="modal fade" id="dynamicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">{{ __('Details') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="text-center py-4"><div class="spinner-border" role="status"></div></div></div>
    </div></div>
</div>
