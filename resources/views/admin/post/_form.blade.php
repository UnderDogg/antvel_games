<div class="row">
  <div class="col-md-8">
    <div class="form-group">
      <label for="title" class="col-md-2 control-label">
        Title
      </label>
      <div class="col-md-10">
        <input type="text" class="form-control" name="title" autofocus
               id="slug-source" value="{{ $title }}">
      </div>
    </div>
    <div class="form-group">
      <label for="slug" class="col-md-2 control-label">
        Slug
      </label>
      <div class="col-md-10">
        <input type="text" class="form-control" name="slug"
               id="slug-target" value="{{ $slug }}">
      </div>
    </div>
    <div class="form-group">
      <label for="content_raw" class="col-md-2 control-label">
        Content
      </label>
      <div class="col-md-10">
        <textarea name="content_raw" class="form-control" data-provide="markdown" placeholder="Voer hier Markdown HTML in" rows="14"
                  id="content_raw">{{ $content_raw }}</textarea>
      </div>
    </div>
  </div>
  <div class="col-md-4">

    <div class="form-group">
      <label for="module_id" class="col-md-3 control-label">
        Module Name (Category)
      </label>
      <div class="col-md-8">
        <select name="module_id" id="module" class="form-control">
          <?php //foreach ($categories as $category): ?>
          <option value=""></option>
          <?php //endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="tags" class="col-md-3 control-label">
        Tags
      </label>
      <div class="col-md-8">
        <select name="tags[]" id="tags" class="input-tags form-control" multiple size="15">
          @foreach ($allTags as $tag)
            <option @if (in_array($tag, $tags)) selected @endif
            value="{{ $tag }}">
              {{ $tag }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
  </div>
</div>