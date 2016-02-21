<div class="box-body">
    <div class='form-group{{ $errors->has("[name]") ? ' has-error' : '' }}'>
        {!! Form::label("[name]", 'Name') !!}
        {!! Form::text("[name]", Input::old("[name]"), ['class' => 'form-control', 'data-slug' => 'source', 'placeholder' => 'name']) !!}
        {!! $errors->first("[name]", '<span class="help-block">:message</span>') !!}
    </div>
    <div class='form-group{{ $errors->has("[slug]") ? ' has-error' : '' }}'>
       {!! Form::label("[slug]", 'Slug') !!}
       {!! Form::text("[slug]", Input::old("[slug]"), ['class' => 'form-control slug', 'data-slug' => 'target', 'placeholder' => 'slug']) !!}
       {!! $errors->first("[slug]", '<span class="help-block">:message</span>') !!}
   </div>
</div>
