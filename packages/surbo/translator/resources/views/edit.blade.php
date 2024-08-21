@php
$languageClass = '';
if (Auth::user()->language === 'ar') {
    $languageClass = 'arabic';
}
@endphp

<div class="translator">
<div class="popup__content_translator" style="margin-bottom: 0px;">
<div class="popup__content--wrap_translator {{ $languageClass }}">
<input type="hidden" value="{{$organization_id}}" name="organization_id"/>
<input type="hidden" value="{{$languageFeature}}" name="feature"/>
<input type="hidden" value="{{$languageType}}" name="type"/>
<label>Default</label>
<span class="default-lang">{{$defaultLanguageData['language_data']}}</span>
</div>
</div>
</div>
<div class="popup__content_translator">

@foreach ($languageData as $lang=>$trans_data)
<div class="popup__content--wrap_translator {{ $languageClass }}">
<label>{{ucwords($lang)}}</label>

<input class="custom-input" @if (ucwords($lang) == 'Arabic') dir="rtl" @else dir="ltr"  @endif data-value="{{$lang}}" name="language[{{$trans_data['language_slug']}}][{{$trans_data['language_abbr']}}]" value="{{$trans_data['language_data']}}">
</div>
@endforeach
</div>

