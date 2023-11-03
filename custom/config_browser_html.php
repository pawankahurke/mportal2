<!-- content starts here  -->

<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <!--     Here you can write extra buttons/actions for the toolbar              -->
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                            </div>
                        </div>

                        <div class="bullDropdown" >
                            <div class="dropdown" id="explain_login">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a  id="config-browser" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('configbrowser', 2); ?>" data-bs-target="configbrowshow">Add Browser Configuration</a>
                                    <a  id="config-kiosk" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('configkiosk', 2); ?>" data-bs-target="configkiosk">Add Kiosk Configuration</a>
                                    <a  id="edit_configurations" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('editconfig', 2); ?>"  onclick="OnEditClick()">Edit Configuration</a>
                                    <a  id="back_configurations" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('backtoConfig', 2); ?>" href="config_browser.php">Back To Configuration</a>
                                    <a  id="msg_configurations" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('msgconfig', 2); ?>"  onclick="messageConfig()">Message Configuration</a>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="config_browser_page" id="config_browser">
                        <div class="card-body" style="display:none">
                            <div class="form-group has-label">
                                <label for="csvgname">
                                    Please select the type
                                </label>
                            </div>

                            <div class="form-check form-check-radio browser">
                                <label class="form-check-label" style="margin-right: 23px;">
                                    <input class="form-check-input" type="radio" id="config-browsersel" name="browsersel" value="option1">
                                    <span class="form-check-sign"></span>
                                    Config Browser
                                </label>

                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="kiosksel" id="config-kiosksel" value="option2">
                                    <span class="form-check-sign"></span>
                                    Config Kiosk
                                </label>
                            </div>
                        </div>

                        <!--<div class="card-body" >-->
                        <input type="hidden" id="selected">
                        <input type="hidden" id="typeselected">
                        <input type="hidden" id="messageId">
                        <input type="hidden" id="messageName">

                            <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="config_grid">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Site Name</th>
                                        <th>Type</th>
                                        <th>Url</th>
                                        <!--<th>Time</th>-->
                                    </tr>
                                </thead>
                            </table>
<!--                            <div id ="messageConfig">
                            <table class="nhl-datatable table table-striped msgconfig" width="100%" data-page-length="25" id="msgconfig_grid" >
                                <thead>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Title</th>
                                        <th>Message Name</th>
                                        <th>Url</th>
                                        <th>Creation Time</th>
                                    </tr>
                                </thead>
                                 <tfoot>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Title</th>
                                        <th>Message Name</th>
                                        <th>Url</th>
                                        <th>Creation Time</th>
                                    </tr>
                                </tfoot>

                            </table>
                            </div>-->
                        <!--</div>-->

<!--Configuration Browser-->
<div id="configbrowshow" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Configuration Browser</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="configbrowshow">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addDeploymentSiteBtn" onclick="addConfigBrowser();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="browsersitename">Site name</label><em class="error" id="err_browsersitename"></em>
                     <select class="selectpicker" data-style="btn btn-info" title="Select Site" data-size="3" id="browsersitename" name="browsersitename">

                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Script Status</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Script" data-size="3" id="scripStatus" name="scripStatus">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="appdownlURL">Application Download URL</label><em class="error" id="err_appdownlURL"></em>
                    <input type="text" name="appdownlURL" id="appdownlURL" class="form-control">
                </div>

                <div class="form-group has-label">
                    <label for="defaultURL">Default URL</label><em class="error" id="err_defaultURL"></em>
                    <input type="text" name="defaultURL" id="defaultURL" class="form-control">
                </div>

                <div class="form-group has-label">
                    <label for="accessRule">Access Rule</label><em class="error" id="err_accessRule"></em>
                    <textarea type="text" name="accessRule" id="accessRule" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="keywordfilter">Keywords</label><em class="error" id="err_keywordfilter"></em>
                    <textarea type="text" name="keywordfilter" id="keywordfilter" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="bookmarks">Bookmarks</label><em class="error" id="err_bookmarks"></em>
                    <textarea type="text" name="bookmarks" id="bookmarks" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label>Monitor Time st</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="monitorTimest" name="monitorTimest">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Restrict File Download</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="restrictFileDownl" name="restrictFileDownl">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Disable Cookies</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="disableCookies" name="disableCookies">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Clear Cache</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="clearCache" name="clearCache">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Disable Bookmark</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="disableBookmark" name="disableBookmark">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Clear Bookmarks</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="clearBookmarks" name="clearBookmarks">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Clear History</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="clearHistory" name="clearHistory">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Disable Copy Paste</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="disableCopyPaste" name="disableCopyPaste">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Blocked PopUp</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="blockedPopUp" name="blockedPopUp">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Disable Fraud Warning</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="disableFraudWarning" name="disableFraudWarning">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Print Page</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="printPage" name="printPage">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                 <div class="form-group has-label">
                    <label for="schedTime">Scheduled Time</label><em class="error" id="err_accessRule"></em>
                    <textarea type="text" name="schedTime" id="schedTime" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                 <div class="form-group has-label">
                    <label for="contentBlocking">Content Blocking</label><em class="error" id="err_accessRule"></em>
                    <textarea type="text" name="contentBlocking" id="contentBlocking" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

            </div>
        </div>
    </div>
</div>

<!--Configuration Kiosk-->
<div id="configkiosk" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Configuration Kiosk</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="configkiosk">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addDeploymentSiteBtn" onclick="addConfigKiosk()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">

                <div class="form-group has-label">
                    <label for="kioskidsitename">Site name</label><em class="error" id="err_browsersitename"></em>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Site" data-size="3" id="kioskidsitename" name="kioskidsitename">

                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Script Status</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Script" data-size="3" id="scripStatuskio" name="scripStatuskio">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Userlist</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="userlist" name="userlist">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="kioskProfiles">Kiosk Profiles</label><em class="error" id="err_kioskProfiles"></em>
                    <textarea type="text" name="kioskProfiles" id="kioskProfiles" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="userConfig">User Configuration</label><em class="error" id="err_userConfig"></em>
                    <textarea type="text" name="userConfig" id="userConfig" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="kioskconfigProfiles">Kiosk Configuration Profiles</label><em class="error" id="err_akioskconfigProfiles"></em>
                    <textarea type="text" name="kioskconfigProfiles" id="kioskconfigProfiles" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="lockScreen">Lock Screen</label><em class="error" id="err_lockScreen"></em>
                    <textarea type="text" name="lockScreen" id="lockScreen" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label>Enable Emergency</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="enableEmergency" name="enableEmergency">
                        <option value="TRUE" selected="">TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="emergencyContacts">Emergency Contacts</label><em class="error" id="err_emergencyContacts"></em>
                    <textarea type="text" name="emergencyContacts" id="emergencyContacts" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>


            </div>
        </div>
    </div>
</div>

<!--Edit Configuration browser-->
<div id="editconfigbrowshow" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Configuration Browser</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="editconfigbrowshow">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="editDeploymentSiteBtn" onclick="updateConfigBrowser();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Update</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">


                <div class="form-group has-label">
                    <label for="editbrowsersitename">Site name</label><em class="error" id="err_editbrowsersitename"></em>
                     <select class="selectpicker" data-style="btn btn-info" title="Select Site" data-size="3" id="editbrowsersitename" name="editbrowsersitename">

                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Script Status</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Script" data-size="3" id="editscripStatus" name="editscripStatus">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="editappdownlURL">Application Download URL</label><em class="error" id="err_editappdownlURL"></em>
                    <input type="text" name="editappdownlURL" id="editappdownlURL" class="form-control">
                </div>

                <div class="form-group has-label">
                    <label for="editdefaultURL">Default URL</label><em class="error" id="err_editdefaultURL"></em>
                    <input type="text" name="editdefaultURL" id="editdefaultURL" class="form-control">
                </div>

                <div class="form-group has-label">
                    <label for="editaccessRule">Access Rule</label><em class="error" id="editerr_accessRule"></em>
                    <textarea type="text" name="editaccessRule" id="editaccessRule" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="editkeywordfilter">Keywords</label><em class="error" id="err_editkeywordfilter"></em>
                    <textarea type="text" name="editkeywordfilter" id="editkeywordfilter" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="editbookmarks">Bookmarks</label><em class="error" id="err_editbookmarks"></em>
                    <textarea type="text" name="editbookmarks" id="editbookmarks" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label>Monitor Time st</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editmonitorTimest" name="editmonitorTimest">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Restrict File Download</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editrestrictFileDownl" name="editrestrictFileDownl">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Disable Cookies</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editdisableCookies" name="editdisableCookies">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Clear Cache</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editclearCache" name="editclearCache">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Disable Bookmark</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editdisableBookmark" name="editdisableBookmark">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Clear Bookmarks</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editclearBookmarks" name="editclearBookmarks">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Clear History</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editclearHistory" name="editclearHistory">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Disable Copy Paste</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editdisableCopyPaste" name="editdisableCopyPaste">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Blocked PopUp</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editblockedPopUp" name="editblockedPopUp">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Disable Fraud Warning</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editdisableFraudWarning" name="editdisableFraudWarning">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Print Page</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editprintPage" name="editprintPage">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                 <div class="form-group has-label">
                    <label for="vschedTime">Scheduled Time</label><em class="error" id="editerr_accessRule"></em>
                    <textarea type="text" name="editschedTime" id="editschedTime" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                 <div class="form-group has-label">
                    <label for="editcontentBlocking">Content Blocking</label><em class="error" id="err_editaccessRule"></em>
                    <textarea type="text" name="editcontentBlocking" id="editcontentBlocking" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

            </div>
        </div>
    </div>
</div>

<!--Edit Configuration Kiosk-->
<div id="editconfigkiosk" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Configuration Kiosk</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="editconfigkiosk">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="editDeploymentSiteBtn" onclick="updateConfigKiosk()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">

                <div class="form-group has-label">
                    <label for="editkioskidsitename">Site name</label><em class="error" id="err_editbrowsersitename"></em>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Site" data-size="3" id="editkioskidsitename" name="editkioskidsitename">

                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Script Status</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Script" data-size="3" id="editscripStatuskio" name="editscripStatuskio">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Userlist</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="edituserlist" name="edituserlist">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="editkioskProfiles">Kiosk Profiles</label><em class="error" id="err_editkioskProfiles"></em>
                    <textarea type="text" name="editkioskProfiles" id="editkioskProfiles" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="edituserConfig">User Configuration</label><em class="error" id="err_edituserConfig"></em>
                    <textarea type="text" name="edituserConfig" id="edituserConfig" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="editkioskconfigProfiles">Kiosk Configuration Profiles</label><em class="error" id="err_editakioskconfigProfiles"></em>
                    <textarea type="text" name="editkioskconfigProfiles" id="editkioskconfigProfiles" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="editlockScreen">Lock Screen</label><em class="error" id="err_editlockScreen"></em>
                    <textarea type="text" name="editlockScreen" id="editlockScreen" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>

                <div class="form-group has-label">
                    <label>Enable Emergency</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editenableEmergency" name="editenableEmergency">
                        <option value="TRUE" >TRUE</option>
                        <option value="FALSE">FALSE</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="editemergencyContacts">Emergency Contacts</label><em class="error" id="err_editemergencyContacts"></em>
                    <textarea type="text" name="editemergencyContacts" id="editemergencyContacts" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
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


