@extends('layouts.admin_master')

@section('css')

    <link rel="stylesheet" href="{{  asset('/adminlte/plugins/datatables/dataTables.bootstrap4.css') }}">

    <style>

        .user_class_active {
            color: #fff;
        }

        .user_class_active > a {
            color: #ff3c41 ! important;
        }

    </style>

@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <section class="content">
        <!-- Exportable Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            EXCEL IMPORT
                        </h2>
                        
                        <form id="languagewords_form" enctype="multipart/form-data" action="{{url('/admin/save/excelimport')}}"
                                  method="post">
                                {{csrf_field()}}

                            <input type="file" class="form-file" name="excel_file" style="margin-bottom: 13px;" required/>

                            <button type="submit" class="btn btn-info" id="add-languagewords" style="margin-bottom: 13px;">Submit</button>

                        </form>
  
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('scripts')
    <script src="{{ asset('/adminlte/plugins/datatables/jquery.dataTables.js') }}"></script>

    <script src="{{ asset('adminlte/plugins/datatables/dataTables.bootstrap4.js') }}"></script>
    <script> 
        
        $('#add-languagewords').on('click', function () {


        var options = {

            beforeSend: function (element) {

                $("#add-languagewords").text('Processing..');

                $("#add-languagewords").prop('disabled', true);

            },
            success: function (response) {

                $("#add-languagewords").prop('disabled', false);

                $("#add-languagewords").text('SAVE');

                if (response.status == "SUCCESS") {

                    swal({
                        title: "Success",
                        text: response.message,
                        type: "success"
                    },
                    function(){
                        window.location.reload();
                    });
                }
                else if (response.status == "FAILED") {

                  swal('Oops',response.message,'warning');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {

                $("#add-languagewords").prop('disabled', false);

                $("#add-languagewords").text('SAVE');

                swal('Oops','Something went to wrong,Please try after some time','warning');
            }
        };
        $("#languagewords_form").ajaxForm(options);
    });
    </script>


    


@endsection
