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

            {{
                $datatable
                    ->headers() // tell the table to render the header in the table
                    ->columns('id', '#') // show # in the header instead of 'id'
                    ->columns('name', 'Full name') // show 'Full name' in the header instead of 'name'
                    ->table(); // render just the table
            }}
            {{
                $datatable
                    ->script() // now render the script
            }}
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
