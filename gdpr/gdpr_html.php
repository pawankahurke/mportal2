<div class="content white-content gdprPage" id="Gdpr">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row clearfix">
                        <div class="col-md-12">
                            <div class="row clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <button class="swal2-confirm btn btn-success btn-sm btn-round buttonGrey" id="gdprServer" aria-label="" onclick="setRetrieveType('server', this);">Server</button>
                                    <button class="swal2-confirm btn btn-success btn-sm btn-round buttonGrey" id="gdprClient" aria-label="" onclick="setRetrieveType('client', this);">Client</button>
                                </div>

                                <div class="clear">&nbsp;</div>

                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="row form-group">
                                        <div class="col-md-12">
                                            <h4><strong>Retrieve user data</strong></h4>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 form-check-radio">
                                            <div class="form-check form-group">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio" name="dataOption" value="user">
                                                    <span class="form-check-sign"></span> User Name
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="username">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="deviceName">
                                        <div class="col-md-3 form-check-radio">
                                            <div class="form-check form-group">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio" name="dataOption" value="device">
                                                    <span class="form-check-sign"></span> Device Name
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="devicename">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="clear">&nbsp;</div>

                                    <!--<div class="row">
                                        <div class="col-md-2 checkbox-radios">
                                            <div class="form-check form-group">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox">
                                                    <span class="form-check-sign"></span> Client Data
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-2 checkbox-radios">
                                            <div class="form-check form-group">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox">
                                                    <span class="form-check-sign"></span> Server Data
                                                </label>
                                            </div>
                                        </div>
                                    </div>-->

                                    <div class="row">
                                        <div class="col-md-12">
                                            <button class="swal2-confirm btn btn-success btn-sm btn-round" id="downloadData" aria-label="">Download Data</button>
                                            <button class="swal2-confirm btn btn-success btn-sm btn-round" id="deleteData" aria-label="">Delete Data</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="clear">&nbsp;</div>

                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="display: none;">
                                    <div class="row form-group">
                                        <div class="col-md-12">
                                            <h4><strong>Anonymize user data</strong></h4>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 form-check-radio">
                                            <div class="form-check form-group">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio">
                                                    <span class="form-check-sign"></span> User Name
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-9">
                                            <div class="form-group">
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 form-check-radio">
                                            <div class="form-check form-group">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio">
                                                    <span class="form-check-sign"></span> Device Name
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-9">
                                            <div class="form-group">
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="clear">&nbsp;</div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <button class="swal2-confirm btn btn-success btn-sm btn-round" id="" aria-label="" onclick="">Anonymize Data</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>