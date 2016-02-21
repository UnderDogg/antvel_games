<div class="row">
  <div class="col-md-8">
    <div class="form-group">
      <label for="name" class="col-md-2 control-label">
        name
      </label>
      <div class="col-md-10">
        <input type="text" class="form-control" name="name" autofocus
               id="slug-source" value="{{ $name }}">
      </div>
    </div>
    <div class="form-group">
      <label for="slug" class="col-md-2 control-label">
        slug
      </label>
      <div class="col-md-10">
        <input type="text" class="form-control" name="slug"
               id="slug-target" value="{{ $slug }}">
      </div>
    </div>
  </div>
</div>