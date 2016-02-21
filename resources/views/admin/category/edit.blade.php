@extends('admin.layouts.admin_template')

@section('content')

    <h1>Editing {{ $category->name }}</h1>

    <hr>

            @include('admin.partials.errors')

    @if(Session::has('flash_message'))
        <div class="alert alert-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif

    {!! Form::model($category, [
        'method' => 'PUT',
        'route' => ['admin.category.update', $category->id]
    ]) !!}



<div class="col-md-8">
    <div class="form-group">
        {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'slug-source']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('slug', 'Slug:', ['class' => 'control-label']) !!}
        {!! Form::text('slug', null, ['class' => 'form-control', 'id' => 'slug-target']) !!}
    </div>

    {!! Form::submit('Update Category', ['class' => 'btn btn-primary']) !!}


              </div>

    {!! Form::close() !!}

@stop

@section('scripts')
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-markdown/2.8.0/css/bootstrap-markdown.min.css">
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-markdown/2.8.0/js/bootstrap-markdown.min.js"></script>

    <script src="/assets/slugify/speakingurl.min.js"></script>
    <script src="/assets/slugify/slugify.min.js"></script>

    <script src="/assets/selectize/selectize.min.js"></script>
  <script src="/assets/selectize/MySelectize.js"></script>
    <script>
        $(function () {
      $("#tags").MySelectize(({
        });

            $('#slug-target').slugify('#slug-source'); // Type as you slug

        });
    </script>

@stop
