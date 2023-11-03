<!-- content starts here  -->      

<div class="content white-content">
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>    
                        </div>
                        </div>

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" onclick="exportEventInformation()">Export</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="audit_selected">
                    <table class="nhl-datatable table table-striped" id="eventTable" width="100%" data-page-length="25">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Description</th>
                                <th>Scrip</th>
                                <th>Client Date and Time</th>
                                <th>Server Time</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Device</th>
                                <th>Description</th>
                                <th>Scrip</th>
                                <th>Client Date and  Time</th>
                                <th>Server Time</th>
                            </tr>
                        </tfoot>
                    </table>
              </div>
              <!-- end content-->
            </div>
            <!--  end card  -->
          </div>
          <!-- end col-md-12 -->
        </div>
        <!-- end row -->
      </div>


<div id="eventEventInfo" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Event information</h4>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<style>
    #eventEventInfo{z-index: 100000 !important}
    #eventEventInfo.show{opacity: 1 !important;}
</style>

