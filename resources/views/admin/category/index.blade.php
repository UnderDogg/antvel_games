@extends('admin.layouts.admin_template')

@section('content')
    <div class="row page-title-row">
        <div class="col-md-6">
            <h3>Categories
                <small>&raquo; Listing</small>
            </h3>
        </div>
        <div class="col-md-6 text-right">
            <a href="/admin/category/create" class="btn btn-success btn-md">
                <i class="fa fa-plus-circle"></i> New Category
            </a>
        </div>
    </div>


    <div class="row">
        <div class="col-sm-12">

            @include('admin.partials.errors')
            @include('admin.partials.success')


            <table class="categories-table table table-bordered table-hover">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>{{ 'Name' }}</th>
                    <th>{{ 'Slug' }}</th>
                    <th data-sortable="false">{{ 'Actions' }}</th>
                </tr>
                </thead>
                <tbody>
                <?php if (isset($categories)): ?>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td>
                        <a href="{{ route('admin.category.edit', [$category->id]) }}">
                            {{ $category->id }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('admin.category.edit', [$category->id]) }}">
                            {{ $category->name }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('admin.category.edit', [$category->id]) }}">
                            {{ $category->slug }}
                        </a>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('admin.category.edit', [$category->id]) }}"
                               class="btn btn-default btn-flat"><i class="fa fa-pencil"></i></a>
                            <button class="btn btn-danger btn-flat" data-toggle="modal"
                                    data-target="#modal-delete-confirmation"
                                    data-action-target="{{ route('admin.category.destroy', [$category->id]) }}"><i
                                        class="fa fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <tfoot>
                <tr>
                    <th>Id</th>
                    <th>{{ 'Name' }}</th>
                    <th>{{ 'Slug' }}</th>
                    <th data-sortable="false">{{ 'Actions' }}</th>
                </tr>
                </tfoot>
            </table>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
@stop

@section('scripts')
    <script>
        $(function () {
            $("#category-table").DataTable({
                order: [[0, "desc"]],
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });
        });
    </script>
@stop
