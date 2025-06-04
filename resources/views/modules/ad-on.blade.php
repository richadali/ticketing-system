@extends('layouts.app_1')

@section('content')

<main id="main" class="main">

    <div class="pagetitle">
      <h1>Add on</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item">Master Data</li>
          <li class="breadcrumb-item active">Add on</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->
    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <!-- Bordered Tabs Justified -->
              <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="myTab" role="tablist">
                <li class="nav-item flex-fill" role="presentation">
                  <button class="nav-link w-100 active" id="ad-on-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-food" type="button" role="tab" aria-controls="home" aria-selected="true"><i class="bi bi-card-list"></i> &nbsp;Add on List</button>
                </li>
                <li class="nav-item flex-fill" role="presentation">
                  <button class="nav-link w-100" id="ad-ons-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-foods" type="button" role="tab" aria-controls="profile" aria-selected="false"><i class="ri-file-add-line"></i>  &nbsp; Create/Edit Add on</button>
                </li>
              </ul>
              <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                <div class="tab-pane fade show active" id="bordered-justified-food" role="tabpanel" aria-labelledby="ad-on-tab">
              
                <div class="alert alert-success alert-block table_msg3" style="display:none;">
                          <strong>Data deleted successfully</strong>
                  </div>
                  <div class="alert alert-danger table_msg4" style="display:none;">
                          <strong>Whoops!</strong> There were some problems with the application.            
                  </div>
                  <div class="alert alert-success table_msg7" style="display:none;">
                    <strong>Status changed successfully</strong>             
                  </div>
                <table class="add-on-table" width="100%"> 
                <thead>
                  <tr>
                    <th class="text-center" scope="col" width="10%">#</th>
                    <th class="text-center" scope="col" width="20%">Name</th>
                    <th class="text-center" scope="col" width="10%">Price</th>
                    <th class="text-center" scope="col" width="10%">Discount</th>
                    <th class="text-center" scope="col" width="15%">Featured</th>
                    <th class="text-center" scope="col" width="15%">Active</th>
                    <th class="text-center" scope="col" width="20%">Actions</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
                </table>
               </div>

                <div class="tab-pane fade" id="bordered-justified-foods" role="tabpanel" aria-labelledby="ad-ons-tab">
                <form id="form-ad-on" method="POST" enctype="multipart/form-data">
                  @csrf
                  <input type="hidden" id="id" name="id">
                  <div class="alert alert-success alert-block table_msg1" style="display:none;">
                            <strong>Data submiited successfully</strong>
                    </div>
                    <div class="alert alert-danger table_msg2" style="display:none;">
                            <strong>Whoops!</strong> There were some problems with your input.            
                    </div>
                    <div class="alert alert-success alert-block table_msg5" style="display:none;">
                            <strong>Data edited successfully</strong>
                    </div>
                    <div class="alert alert-danger table_msg6" style="display:none;">
                            <strong>Whoops!</strong> There were some problems with your input.            
                    </div>
                    <div class="col-md-6">
                      <label for="item_name" class="form-label"><b>Name</b></label>
                      <input type="text" class="form-control" id="item_name" name="item_name" required>
                    </div>
                    <div class="col-md-6">
                      <label for="price" class="form-label"><b>Price</b></label>
                      <input type="number" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="col-md-6">
                      <label for="discount" class="form-label"><b>Discount(%)</b></label>
                      <input type="number" class="form-control" id="discount" name="discount" required>
                    </div>
                    <div class="col-md-6">
                      <label for="desc" class="form-label"><b>Description</b></label>
                      <textarea class="form-control" id="desc" name="desc" required></textarea>
                    </div>
                    <div class="col-md-6">
                      <label for="feature" class="form-label"><b>Feature</b></label>
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="feature" id="feature">
                      </div> 
                      <div class="col-md-6">
                      <label for="active" class="form-label"><b>Active</b></label>
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" id="active">
                      </div>                   
                   </div>
                    <div class="col-md-6">
                      <label for="item_img" class="form-label"><b>Upload image</b></label>
                      <input type="file" class="form-control" id="item_img" name="item_img" required>
                      <span style="color:red"><i>*Support only jpeg,png,jpg,gif</i></span>
                    </div>
                    <br>
                    <div calss="col-sm-6" style="display:none" id="has_image">
                      <img src="" id="item_imgs" class="card-img-bottom" alt="..."  width="100" height="200">
                    </div>
                  <br>
                    <div class="text-center">
                      <button type="submit"  class="btn btn-primary btn-sm">Submit</button>
                      <button type="reset" class="btn btn-secondary btn-sm">Reset</button>
                    </div>
              </form>
                </div>
                
              </div><!-- End Bordered Tabs Justified -->

            </div>
        </div>
      </div>
    </section>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  </main>
  <script src="{{asset('assets/js/modules/item.js')}}"></script>
@endsection