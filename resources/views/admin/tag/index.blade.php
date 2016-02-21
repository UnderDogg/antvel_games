@extends('admin.layouts.admin_template')

@section('content')
    <div class="container-fluid">
        <div class="row page-title-row">
            <div class="col-md-6">
                <h3>Tags
                    <small>&raquo; Listing</small>
                </h3>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('admin.tag.create') }}" class="btn btn-success btn-md">
                    <i class="fa fa-plus-circle"></i> New Tag
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">

                @include('admin.partials.errors')
                @include('admin.partials.success')

                <table id="tags-table" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th data-sortable="false">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($tags as $tag)
                        <tr>
                            <td>{{ $tag->tag }}</td>
                            <td>
                                <a href="/admin/tag/{{ $tag->id }}/edit"
                                   class="btn btn-xs btn-info">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Title</th>
                        <th data-sortable="false">Actions</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop

@section('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>c</code></dt>
        <dd>{{ 'tag.title.create tag' }}</dd>
    </dl>
@stop



@section('scripts')
    <script>
        $(function () {
            $("#tags-table").DataTable({
                "paginate": true,
                "lengthChange": true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "filter": false,
                "sort": false,
                "info": true,
                "autoWidth": true,
                "order": [[0, "desc"]]
            });
        });
    </script>
@stop