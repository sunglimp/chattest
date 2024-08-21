@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<div class="popup popup__container" id="chat-transfer">
    <div class="popup__wrapper">
        <a class="close-btn {{ $languageClass }}" id="close-btn-tags"><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">Chat Transfer</div>
        <div class="popup__content">
            <div class="popup__timer__heading">
                Please Select time for Transfer Chat
            </div>
            <form>
            <div class="popup__timer__content">
                <div class="popup__timer__wrapper">
                    <label for="">Hours<span class="astrick">*</span></label>
                    <input class="custom-input" type="number" min=0 max=23 />
                </div>
                <div class="popup__timer__wrapper">
                    <label for="">Minutes<span class="astrick">*</span></label>
                    <input class="custom-input" type="number" min=0 max=60 />
                </div>
                <div class="popup__timer__wrapper">
                    <label for="">Seconds<span class="astrick">*</span></label>
                    <input class="custom-input" type="number" min=0 max=60 />
                </div>
            </div>
            <p class="warning-text warning-text-timer" ></p>
            <div class="buttons__all margin-top-2">
                <button type="button" class="custom-button custom-button-green" id="cancel">Cancel</button>
                <button type=form class="custom-button custom-button-blue">Submit</button>
            </div>
            </form>
        </div>
    </div>
