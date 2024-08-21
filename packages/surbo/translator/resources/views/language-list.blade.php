@php
$languageClass = '';
if (Auth::user()->language === 'ar') {
    $languageClass = 'arabic';
}
@endphp

<div class="content__wrapper content__wrapper--organization margin-top-2">
    <div class="margin-top-2 table-conatiner-translator">
        <div class="header__text">{{$languageType}} > <span style="color:#ea5455"> {{$languageFeature}}</span></div>
        <div class="margin-top-2">
            <input type="text" class="custom-input custom-input-search {{ $languageClass }}" id="datatable-search" placeholder="Search">

        </div>
        <table class="table table-sorting image-list margin-top-2  {{ $languageClass }}" id="customize-field-table">
            <thead>
                <tr>
                    <th class="">Default</th>
                    @foreach ($orgLanguages as $language)
                    <th class="">{{$language}}</th>
                    @endforeach
                    <th class="">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
var lang_table_columns = '{!! $tableColumns !!}';
</script>


