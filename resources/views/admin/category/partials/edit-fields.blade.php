<div class="box-body">
    <div class='form-group{{ $errors->has("[name]") ? ' has-error' : '' }}'>
        {!! Form::label("name", trans('blog::category.form.name')) !!}
        <?php $old = $category->name ?>
        {!! Form::text("name", Input::old("name", $old), ['class' => 'form-control', 'data-slug' => 'source', 'placeholder' => trans('blog::category.form.name')]) !!}
        {!! $errors->first("name", '<span class="help-block">:message</span>') !!}
    </div>
    <div class='form-group{{ $errors->has("[slug]") ? ' has-error' : '' }}'>
           {!! Form::label("[slug]", trans('blog::category.form.slug')) !!}
            <?php $old = $category->slug ?>
           {!! Form::text("slug", Input::old("slug", $old), ['class' => 'form-control slug', 'data-slug' => 'target', 'placeholder' => trans('blog::category.form.slug')]) !!}
           {!! $errors->first("slug", '<span class="help-block">:message</span>') !!}
       </div>
</div>
