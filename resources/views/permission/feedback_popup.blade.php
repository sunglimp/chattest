
<div class="popup popup__container" id="chat-feedback__popup" >
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">Chat Feedback</div>
        
        <div class="popup__content">
        <form>
        <div class="select-custom">
            <select name="feedback" id="feedback" class="select">
                <option value="NPS" @if($data->feedback == 'NPS') selected @endif>NPS</option>
                <option value="CES" @if($data->feedback == 'CES') selected @endif>CES</option>
            </select>
        </div>
        <div class="buttons__all">
            <button type="button" class="custom-button custom-button-green" id="cancel">Cancel</button>
            <button type="button" class="custom-button custom-button-blue" id="update-chat-feedback-button">Submit</button>
        </div>
        </form>
        </div>
    </div>
</div>
