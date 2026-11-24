{{-- Generic Bootstrap modal shell. Pages push their own modal markup via @push('modals') if needed; this keeps the old @include('frontend.partials.modal') call working. --}}
<div class="modal fade" id="atsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="atsModalTitle">{{ setting('app_name', 'ATS Skill') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body" id="atsModalBody"></div>
        </div>
    </div>
</div>
@stack('modals')
