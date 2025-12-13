@extends('layouts.app')

@section('header')
    @include('partials.sections.header')
@endsection

@section('content')
    <div class="container py-5">
        @foreach(Growtype_Form_Profile::custom_pages() as $page)

            @if($page['key'] === 'profile')
                @continue
            @endif

            <a href="{!! home_url($page['url']) !!}" class="btn-primary">{!! $page['title'] ?? 'test' !!}</a>
        @endforeach
    </div>
@endsection

@section('footer')
    @include('partials.sections.footer')
@endsection
