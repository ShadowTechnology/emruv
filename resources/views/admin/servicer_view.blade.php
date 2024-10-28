@extends('layouts.admin_master')

@section('css')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <style type="text/css">
        
        .title{

            font-size: 18px;
            font-weight: bold;
            color: #3f93af;

        }

    </style>

@endsection

@section('content')

     <section class="content">
      <div class="container-fluid">

            <div class="row">
              <!-- left column -->

           
                <div class="col-md-6">
                    <!-- general form elements -->
                    <div class="card card-primary">
                      <div class="card-header">
                        <h3 class="card-title">Account</h3>
                      </div>
                      <!-- /.card-header -->
                      <!-- form start -->
                        <div class="card-body">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Your Name :</label>  
                                {{$user->name}}
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">Mobile :</label> 
                                {{$user->mobile}}
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">Email address :</label>  
                               {{$user->email}}
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">Address :</label> 
                                {{$servicer->current_address}}
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">DOB :</label> 
                                {{$user->dob}}
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">Gender :</label> 
                                {{$user->gender}}
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">Referal Code :</label> 
                                {{$user->referal_code}}
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">Country :</label> 
                               {{$user_array['country_code']}}
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">Status :</label> 
                               {{$user->status}}
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">Joined On :</label> 
                               {{date('Y-m-d H:i:s', strtotime($user->created_at))}}
                            </div>
                            
                            <div class="form-group"> 
                                <label for="exampleInputEmail1">Profile Image :</label>  <br/>
                                @if(isset($servicer_array['documents']) && !empty($servicer_array['documents']['profile_image']))
                                    <img src="{{$servicer_array['documents']['profile_image']}}" height="250" width="250">
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->

                    <!-- Form Element sizes -->
                    <div class="card card-success d-none">
                      <div class="card-header">
                        <h3 class="card-title">Timings</h3>
                      </div>
                      <div class="card-body">
                            
                            <div class="form-group">
                                <label>Service Start Time :</label> 
                                {{$servicer->start_time}}
                            </div>

                            <div class="form-group">
                                <label>Service End Time :</label> 
                                {{$servicer->end_time}}
                            </div>

                            <div class="form-group">
                                <label>Available for Emergency :</label> 
                                @if($servicer->emergency_available == 1) Yes @else No @endif
                            </div>

                            <div class="form-group">
                                <label>Requests per Slot :</label> 
                                {{$servicer->requests_per_slot}}
                            </div>

                            <div class="form-group">
                                <label>Working Days :</label>  
                                {{$servicer_array['service_days']}}
                            </div>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

                    <!-- Form Element sizes -->
                    <div class="card card-success">
                      <div class="card-header">
                        <h3 class="card-title">Emergency Contacts</h3>
                      </div>
                      <div class="card-body">
                            
                            <div class="form-group">
                                <label>Contact Name:</label> 
                                {{$servicer->emergency_contact_name}}
                            </div>

                            <div class="form-group">
                                <label>Number :</label> 
                                {{$servicer->emergency_contact_number}}
                            </div>

                            <div class="form-group">
                                <label>Relationship :</label> 
                                {{$servicer->emergency_contact_relationship}}
                            </div>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Service Zones</h3>
                        </div>
                        <div class="card-body">
                        @if(!empty($servicer_array['location_string'])) 
                             {{$servicer_array['location_string']}}
                        @endif   
                        </div>  
                    </div>

                    <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">Service Details</h3>
                            </div>
                            <div class="card-body">
                                <?php // echo "<pre>";print_r($servicer_array); exit; ?>
                            @if(!empty($categories) && is_object($categories) && count($categories)>0) 
                                @foreach($categories as $cat) 
                                <div class="form-group">
                                    <b><label for="exampleInputEmail1">Category :</label>
                                     {{$cat->name}}</b>
                                </div>
                                @foreach($cat->subCategories as $subcat) 
                                <div class="form-group ml-3">
                                    {{$subcat->name}}
                                     @foreach($subcat['services'] as $subcatservices) 
                                        <div class="form-group ml-3"> 
                                          @if(isset($subcatservices['subServices']) && count($subcatservices['subServices']) > 0)
                                            <br>{{$subcatservices->name}} 
                                            <?php echo (!empty($subcatservices->description)) ?  "(".$subcatservices->description.")" : ''; ?>
                                            <div class="form-group ml-3">
                                              <table>
                                                <thead>
                                                  <th>Name</th>
                                                  <!-- <th>Service Type</th> -->
                                                    <th>Hour Price</th>
                                                    <th>Fixed Price</th>
                                                </thead>
                                              @foreach($subcatservices['subServices'] as $subservices) 
                                                <tbody>
                                                  @foreach($subservices['servicersdetails'] as $details) 
                                                    <tr>
                                                      <td>{{$details->name}}</td>
                                                      <!-- <td>{{$details->service_type}}</td> -->
                                                      <td style="text-align: right;">{{$details->hour_price}}</td>
                                                      <td style="text-align: right;">{{$details->fixed_price}}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                              @endforeach
                                              </table>
                                            </div>
                                          @endif
                                        </div>
                                      @endforeach
                                </div>
                                @endforeach
                              @endforeach
                              
                            @endif   
                            </div> 
                            <div class="card-body d-none">

                                <div class="form-group">
                                    <label for="exampleInputEmail1">Category</label>
                                     <br>{{$servicer_array['sel_categories']}}
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">Sub Categories</label>
                                     <br>{{$servicer_array['sel_sub_categories']}}
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">Services</label>
                                     <br>{{$servicer_array['sel_services']}}
                                </div>

                            </div>

                        </div>
                    
                  </div>
                  <!--/.col (left) -->
                  <!-- right column -->
                  <div class="col-md-6">
                        <!-- Horizontal Form -->
                        <div class="card card-info">
                          <div class="card-header">
                            <h3 class="card-title">Provider Details</h3>
                          </div>
                    
                            <div class="card-body">
                                <div class="form-group d-none">
                                    <label>Type :</label>
                                    
                                    {{$servicer->service_provider_type}}
                                </div>
                                <div class="form-group">
                                    <label>Experience :</label>
                                      <?php 
                                     $prov = ['1'=>'Professional', '2'=>'Non - Professional','3'=>'Student'];
                                     $sts = [];
                                     $st = $servicer->experience;
                                     if(!empty($st)) {
                                      $st = explode(',', $servicer->experience); 
                                      foreach ($st as $key => $value) {
                                       $sts[] = $prov[$value];
                                      }
                                     }
                                     echo implode(', ', $sts); ?>
                                </div>

                                <div class="form-group">
                                    <label>Experience Description :</label>
                                     
                                     {{$servicer_array['experience_description']}}
                                </div>

                                <div class="form-group">
                                    <label>Location :</label>
                                     
                                     {{$servicer_array['house']}}
                                </div>

                                <div class="form-group">
                                    <label>City :</label>
                                     
                                     {{$servicer_array['city']}}
                                </div>

                                 <div class="form-group">
                                    <label>Area :</label>
                                     
                                     {{$servicer->locality}}
                                </div>

                                <div class="form-group">
                                    <label>Pincode :</label>
                                     
                                     {{$servicer->pincode}}
                                </div>

                                <div class="form-group">
                                    <label>Current Address :</label>
                                     
                                     {{$servicer_array['current_address']}}
                                </div>

                                <div class="form-group">
                                    <label>Current House :</label>
                                     
                                     {{$servicer_array['current_house']}}
                                </div>

                                <div class="form-group">
                                    <label>Current Landmark :</label>
                                     
                                     {{$servicer_array['current_landmark']}}
                                </div>
                            </div>
                        </div>

                          <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Upload Documents</h3>
                            </div>
                                
                            <div class="card-body">

                                <div class="form-group d-none">
                                    <label for="exampleInputEmail1">Type of Proof :</label>
                                    
                                    @if($servicer->type_of_proof == 1)
                                      National Id
                                    @elseif($servicer->type_of_proof == 2)
                                      Passport
                                    @elseif($servicer->type_of_proof == 3)
                                      Others
                                    @endif
                                </div> 
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Aadhar Proof :</label>
                                    <br>@if(isset($servicer_array['documents']) && !empty($servicer_array['documents']['id_proof_front']))
                                    
                                        <img src="{{$servicer_array['documents']['id_proof_front']}}" height="250" width="250">
                                      
                                    @endif

                                    @if(isset($servicer_array['documents']) && !empty($servicer_array['documents']['id_proof_back']))
                                    
                                        <img src="{{$servicer_array['documents']['id_proof_front']}}" height="250" width="250">
                                      
                                    @endif
                                </div><?php // echo "<pre>"; print_r($servicer_array['documents']); exit; ?>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">Driving License Proof :</label>
                                    <br>@if(isset($servicer_array['documents']) && !empty($servicer_array['documents']['driving_license_front']))
                                    
                                        <img src="{{$servicer_array['documents']['driving_license_front']}}" height="250" width="250">
                                      
                                    @endif

                                    @if(isset($servicer_array['documents']) && !empty($servicer_array['documents']['driving_license_back']))
                                    
                                        <img src="{{$servicer_array['documents']['driving_license_back']}}" height="250" width="250">
                                      
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">NOC Front :</label>
                                    <br>@if(isset($servicer_array['documents']) && !empty($servicer_array['documents']['noc_front']))
                                    
                                        <img src="{{$servicer_array['documents']['noc_front']}}" height="250" width="250">
                                      
                                    @endif
                                </div>


                                <form id="user-form"  action="{{url('/admin/approve/servicer')}}" method="post">
                                {{csrf_field()}}
                                <input type="hidden"  id="user_id" name="user_id" value="{{$servicer_array['user_id']}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Approval Status :</label> 
                                    <select class="form-control" name="approve_status" id="product_category">
                              <option value="APPROVED" {{ ($servicer_array['servicer_approve_status'] == "APPROVED") ?  "selected" : '' }}>Approved</option>
                              <option value="UNAPPROVED" {{ ($servicer_array['servicer_approve_status'] == "UNAPPROVED" || $servicer_array['servicer_approve_status'] == "") ? "selected" : '' }}>Un Approved</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-info" id="approve-user">Submit</button>
                              </form>
                            </div>

                        </div>

                    <!-- /.card -->
                    </div>
                  <!--/.col (right) -->
            </div>
            <input type="hidden" name="sel_sub_cat_ids" id="sel_sub_cat_ids" value="{{$servicer_array['sub_category_ids']}}">
            <input action="action" onclick="window.history.go(-1); return false;" type="button" value="BACK" class="btn btn-info waves-effect" />

           
        </form>
         
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>

@endsection
@section('scripts')



    <script>
        $('#approve-user').on('click', function () {

            var sel_sub_cat_ids = $('#sel_sub_cat_ids').val();
            if($.trim(sel_sub_cat_ids) == '') {
                swal("Oops!", 'No Valid Services added', "info");
            }   else {
                var $edituser = $("#approve-user");
                var options = {

                    beforeSend: function (element) {

                        $edituser.text('Processing..');

                        $edituser.prop('disabled', true);

                    },
                    success: function (response) {


                        $edituser.text('SUBMIT');

                        $edituser.prop('disabled', false);

                        if (response.status == "SUCCESS") {

                            swal({
                                title: "Info!",
                                text: "The Servicer Status has been updated",
                                type: "success",

                            }, function () {
                                window.location.reload();
                            });

                        }
                        else if (response.status == "FAILED") {

                            swal("Oops!", response.message, "info");
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {

                        $edituser.prop('disabled', false);

                        $edituser.text('SUBMIT');

                        swal("Oops!", 'Sorry could not process your request', "error");
                    }
                };
                $("#user-form").ajaxForm(options);
            }

            
        });


    </script>

@endsection
