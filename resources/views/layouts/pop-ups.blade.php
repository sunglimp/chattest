@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<div class="popup popup__container" id="delete__popup">
    <div class="popup__wrapper popup__small">
        <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
        <div class="popup__content">
            <span id="confirm-message"></span>
            <div class="buttons__all">
                <button type="button" class="custom-button custom-button-primary" id="yes_confirmation" value=''>Yes</button>
                <button type="button" class="custom-button" id="cancel">No</button>
            </div>
        </div>
    </div>
</div>


