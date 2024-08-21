@extends('app')
@section('heading','Dashboard')
@section('title','Dashboard')
@section('main-content')
@endsection
@push('custom-scripts')
<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
  	<script src="/js/main.js"></script>
	<script>
		$(".loader").hide();
	</script>
@endpush