<div class="content white-content">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card" style="padding: 5px;">
                <div class="card-body" style="height: 500px; overflow-y: scroll">
                    <div class="row">
                        <?php
                        //   nhRole::checkRoleForPage('sso');
                        $res = true; // nhRole::checkModulePrivilege('sso');
                        if ($res) {
                        ?>
                            <div class="col-md-3" style="margin-top: 15px;">
                                <span>Enable/Disable SSO ? </span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <label class="switch" style="top: -6px;" data-qa="sso-switch-btn">
                                    <input type="checkbox" name="ssoStatus" class="checkbox" id="ssoVal" onchange="updateSSOStatus();" />
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="col-md-6" style="border-left: 1px solid lightgrey; padding-left: 5%;">
                                <h5>Select Single sign-on method</h5>
                                <div class="form-check form-check-radio global">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="sso-type" id="sso-oauth" checked="" value="OAUTH">
                                        <span class="form-check-sign"></span>
                                        <span>OAUTH</span>
                                    </label>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="sso-type" id="sso-saml" value="SAML">
                                        <span class="form-check-sign"></span>
                                        <span>SAML</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">

                            </div>
                    </div>

                    <hr />
                    <div class="row clearfix">
                        <div id="absoLoader" style="display:none">&nbsp;<img src="../assets/img/nanohealLoader.gif" style="width: 71px;"></div>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="sso_oauth_cnt" style="/*display: none*/">
                            <h5><b>OAUTH CONFIGURATION DETAILS</b></h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="label">Company Name</label>
                                                <input type="text" class="form-control" id="oauth_company_name" name="oauth_company_name" value="" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="label">IdP Full Name</label>
                                                <input type="text" class="form-control" id="oauth_idp_name" name="oauth_idp_name" value="" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="label">Authorize URL</label>
                                        <input type="text" class="form-control" id="oauth_auth_url" name="oauth_auth_url" value="" />
                                    </div>

                                    <div class="form-group">
                                        <label class="label">Access URL</label>
                                        <input type="text" class="form-control" id="oauth_access_url" name="oauth_access_url" value="" />
                                    </div>

                                    <div class="form-group">
                                        <label class="label">Client ID</label>
                                        <input type="text" class="form-control" id="oauth_client_id" name="oauth_client_id" value="" />
                                    </div>

                                    <div class="form-group">
                                        <label class="label">Client Secret</label>
                                        <input type="password" class="form-control" id="oauth_client_secret" name="oauth_client_secret" value="" />
                                    </div>

                                    <div class="form-group">
                                        <label class="label">Scope <i>(comma / space delimited)</i></label>
                                        <input type="text" class="form-control" id="oauth_scope" name="oauth_scope" value="" />
                                    </div>

                                    <div class="form-group">
                                        <label class="label">Resource URL</label>
                                        <input type="text" class="form-control" id="oauth_resource_url" name="oauth_resource_url" value="" />
                                    </div>

                                    <div class="form-group" data-required="true">
                                        <label class="label">Oauth Version</label>
                                        <select class="selectpicker" id="oauth_version" name="oauth_version">
                                            <option value="1">OAuth 1</option>
                                            <option value="2" selected="">OAuth 2</option>
                                        </select>
                                        <!--<input type="text" class="form-control" id="oauth_version" name="oauth_version" value="" />-->
                                    </div>

                                    <!--<div class="form-group">
                                        <label class="label">Tenant ID <i>(optional)</i></label>
                                        <input type="text" class="form-control" id="oauth_tenant_id" name="oauth_tenant_id" value="" />
                                    </div>-->

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-1">
                                                <button type="button" class="swal2-confirm btn btn-alert btn-sm btn-sso" id="oauth_verify_btn">Test / Verify</button>
                                            </div>
                                            <div class="col-md-4" id="oauth_verify_stbox" style="margin-top: 12px;">
                                                <span>OAuth Verification Status : </span>
                                                <span id="oauth_vstatus" style="color: red; font-weight: bold;">Pending</span>
                                            </div>
                                            <div class="col-md-7">
                                            </div>
                                        </div>
                                        <br />
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <button type="button" class="swal2-confirm btn btn-success btn-sm btn-sso" id="oauth_save_btn">Save Configuration</button>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="swal2-confirm btn btn-success btn-sm btn-sso sso_clear_btn">Clear Configuration</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="sso_saml_cnt" style="display: none;">
                            <h5><b>SAML CONFIGURATION DETAILS</b></h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="label">Company Name</label>
                                                <input type="text" class="form-control" id="saml_company_name" name="saml_company_name" value="" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="label">IdP Full Name</label>
                                                <input type="text" class="form-control" id="saml_idp_name" name="saml_idp_name" value="" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="label">IdP metadata URL</label>
                                        <input type="text" class="form-control" id="saml_idp_metadata_url" name="saml_idp_metadata_url" value="" />
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-1">
                                                <button type="button" class="swal2-confirm btn btn-alert btn-sm btn-sso" id="saml_get_metadata">Get Metadata</button>
                                            </div>
                                            <div class="col-md-4 txt-loader" id="loader_box" style="display: none;">
                                                Fetching metadata. Please wait...
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="label">IdP Metadata <i>(editable)</i></label>
                                        <textarea class="form-control" id="saml_idp_metadata" name="saml_idp_metadata" value=""></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label class="label">SP Entity ID</label>
                                        <input type="text" class="form-control" id="saml_sp_entity_id" name="saml_sp_entity_id" value="" />
                                    </div>

                                    <!--<div class="form-group">
                                        <label class="label">ACS URL <i>(callback)</i></label>
                                        <input type="text" class="form-control" id="saml_acs_url" name="saml_acs_url" value="" />
                                    </div>-->

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-1">
                                                <button type="button" class="swal2-confirm btn btn-alert btn-sm btn-sso" id="saml_verify_btn">Test / Verify</button>
                                            </div>
                                            <div class="col-md-4" id="saml_verify_stbox" style="margin-top: 12px; display: none;opacity: 0;">
                                                <span>SAML Verification Status : </span>
                                                <span id="saml_vstatus" style="color: red; font-weight: bold;">Pending</span>
                                            </div>
                                            <div class="col-md-7">
                                            </div>
                                        </div>
                                        <br />
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <button type="button" class="swal2-confirm btn btn-success btn-sm btn-sso" id="saml_save_btn">Save Configuration</button>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="swal2-confirm btn btn-success btn-sm btn-sso sso_clear_btn">Clear Configuration</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php
                        }
                ?>
                </div>
            </div>
        </div>
    </div>
</div>