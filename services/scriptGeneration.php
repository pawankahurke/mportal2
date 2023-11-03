<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
require_once("../include/common_functions.php");

nhRole::dieIfnoRoles(['darts']); // roles: darts
if (url::requestToAny('function') != '') { // roles: darts
    $function  = url::requestToText('function'); // roles: darts
    $function($dartnum);
}

function getHtmlContent()
{
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $rparent = $_SESSION['rparentName'];

    $scripnum = url::requestToText('scripnum');

    $db = pdo_connect();

    $scripNameSql = $db->prepare("select name from " . $GLOBALS['PREFIX'] . "iosprofile.Scripsnew where num = ?");

    $scripNameSql->execute([$scripnum]);
    $scripNameSqlRes = $scripNameSql->fetch();
    $scripName = $scripNameSqlRes['name'];


    if ($searchType == 'ServiceTag' && $scripnum != '2001' && $scripnum != '4001') {

        $rowData = $db->prepare("SELECT t1.*, t2.varValue as value FROM mdmVariables t1 LEFT JOIN mdmModifiedVariables " .
            "t2 on t1.varName = t2.varName WHERE t1.dartNum = ? and t2.scope=? and t2.scopeValue=? group by varName");
        $rowData->execute([$scripnum, $searchType, $searchValue]);
        $scripData = $rowData->fetchAll();

        if (safe_count($scripData) == 0) {
            $rowData = $db->prepare("SELECT t1.*, t2.varValue as value FROM mdmVariables t1 LEFT JOIN mdmModifiedVariables " .
                "t2 on t1.varName = t2.varName WHERE t1.dartNum = ? and t2.scope='Sites' and t2.scopeValue=? group by varName");
            $rowData->execute([$scripnum, $rparent]);
            $scripData = $rowData->fetchAll();
        }
    } else if ($scripnum != '2001' && $scripnum != '4001') {
        $rowData = $db->prepare("SELECT t1.*, t2.varValue as value FROM mdmVariables t1 LEFT JOIN mdmModifiedVariables " .
            "t2 on t1.varName = t2.varName WHERE t1.dartNum = ? and t2.scope=? and t2.scopeValue=? group by varName");
        $rowData->execute([$scripnum, $searchType, $searchValue]);
        $scripData = $rowData->fetchAll();
    }
    if (safe_count($scripData) == 0 || $scripnum == '2001' || $scripnum == '4001') {
        $rowData = $db->prepare("SELECT t1.*, t2.varValue as value FROM mdmVariables t1 LEFT JOIN mdmModifiedVariables " .
            "t2 on t1.varName = t2.varName WHERE t1.dartNum = ? group by varName");
        $rowData->execute([$scripnum]);
        $scripData = $rowData->fetchAll();
    }
    $selectOpt = $db->prepare("SELECT t1.varType as Type, t2.* FROM mdmVariables t1 LEFT JOIN mdmSelectMap " .
        "t2 on t1.varName = t2.varName AND t1.dartNum = t2.dartNum WHERE t1.varType = 4 ");
    echo $selectOpt;
    $selectOpt->execute();
    $selectVal = $selectOpt->fetchAll();
    $dartFile = '';
    if ($scripnum > 2000 && $scripnum < 3000) {
        $dartFile = 'generateIosXML.php';
        $function = 'iosSubmit()';
        $commandFun = 'commandExecute(this)';
    } else if ($scripnum > 3000 && $scripnum < 4000) {
        $dartFile = 'generateWinXML.php';
        $function = 'winSubmit()';
        $commandFun = 'commandExecuteWin(this)';
    } else if ($scripnum > 4000 && $scripnum < 5000) {
        $dartFile = 'generateMacXML.php';
        $function = 'macSubmit()';
        $commandFun = 'commandExecuteMac(this)';
    }

    $str =  '<form id="script_form" method="POST" action="' . $dartFile . '" target="_blank">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 customscroll">
                                <table class="dt-responsive hover order-table nowrap" id="iosconfigGrid" width="100%" data-page-length="25">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <input type="hidden" id="dartNum" name="dartNum" value="' . $scripnum . '">
                                    <input type="hidden" id="dartName" name="dartName" value="' . str_replace(' ', '', $scripName) . '">';
    if ($scripnum == '2001' || $scripnum == '4001') {
        foreach ($scripData as $key => $value) {
            $varName = $value['varName'];
            $varDesc = $value['varDescription'];
            $varType = $value['varType'];
            $str .= '<tr id="' . $varName . '_tr">
                                                <td>
                                                    <p class="varName" >' . $varDesc . '</p>
                                                </td>
                                                <td>
                                                    <input type="button" class="swal2-confirm btn btn-success btn-sm rightBtn" name="' . $varName . '_profile_' . $varType . '" id="' . $varName . '" value="Execute" onclick="' . $commandFun . ';">
                                                    <input type="hidden" class="mylabelValue" name="' . $varName . '_profile_' . $varType . '" id="' . $varName . '" >
                                                </td>
                                            </tr>';
        }
        $str .= '</tbody>
                                        </table>
                                    </div>
                                </form>';
    } else {
        if ($scripnum != '2025' && $scripnum != '2023' && $scripnum != '2027') {

            $str .= '<tr>
                                                <td>
                                                    <p class="varName" >Scrip Enabled</p>
                                                </td>
                                                <td>
                                                    <!--&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="mylabelValue" id="scrip_enabled" name="scrip_enabled" value ="" />-->
                                                    <div class="checkbox">
                                                        <label><input class="report_detail" type="checkbox" name="scrip_enabled" id="scrip_enabled" value=""><span class="checkbox-material"><span class="check"></span></span></label>
                                                    </div>
                                                </td>
                                            </tr>';
        }
        foreach ($scripData as $key => $value) {
            $defaultVal = isset($value['value']) ? $value['value'] : $value['defaultValue'];
            $varName = $value['varName'];
            $varDesc = $value['varDescription'];
            $varType = $value['varType'];
            $min = $value['min'];
            $max = $value['max'];

            $str .= '<tr id="' . $varName . '_tr">
                                                <td>
                                                    <p class="varName" >' . $varDesc . '</p>
                                                </td>';

            if ($varType == 1) {
                $str .= '<td>';
                if ($defaultVal == 1) {
                    $str .= '<div class="checkbox">
                                                            <label><input class="report_detail" type="checkbox" name="' . $varName . '_profile_' . $varType . '" id="' . $varName . '" value="1" onclick="setValue(this);" checked><span class="checkbox-material"><span class="check"></span></span></label>
                                                            </div>';
                } else {
                    $str .= '<input type="hidden" class="mylabelValue" name="' . $varName . '_profile_' . $varType . '" >
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input class="report_detail" type="checkbox" name="' . $varName . '_profile_' . $varType . '" id="' . $varName . '" value="' . $defaultVal . '" onclick="setValue(this);">
                                                                        <span class="checkbox-material"><span class="check"></span></span>
                                                                </label>
                                                            </div>';
                }
                $str .= '</td>';
            } elseif ($value['varType'] == 2 || $value['varType'] == 8) {
                $str .= '<td>
                                                    <input class="form-control" type="text" name="' . $varName . '_profile_' . $varType . '" id="' . $varName . '" value="' . $defaultVal . '">
                                                    </td>';
            } elseif ($value['varType'] == 3) {
                $str .= '<td>
                                                        &nbsp;&nbsp;&nbsp;&nbsp;<input type="number" class="mylabelValue" name="' . $varName . '_profile_' . $varType . '" id="' . $varName . '" min="' . $min . '" max="' . $max . '" value="' . $defaultVal . '">
                                                    </td>';
            } elseif ($value['varType'] == 4) {

                $str .= '<td>
                                                        &nbsp;&nbsp;&nbsp;&nbsp;<select form-control name="' . $varName . '_profile_' . $varType . '" id="' . $varName . '" style="margin-left: -5%;"> ';

                $db = pdo_connect();
                $selectOpt = $db->prepare("SELECT t1.varType as Type, t2.* FROM " . $GLOBALS['PREFIX'] . "iosprofile.mdmVariables t1 LEFT JOIN " . $GLOBALS['PREFIX'] . "iosprofile.mdmSelectMap " .
                    "t2 on t1.varName = t2.varName AND t1.dartNum = t2.dartNum WHERE t1.varName = ? ");
                $selectOpt->execute([$varName]);
                $selectVal = $selectOpt->fetch();

                $options = $selectVal['options'];
                $opt = explode(',', $options);
                $optVal = $selectVal['value'];
                $optValue = explode(',', $optVal);

                foreach ($opt as $index => $val) {
                    if ($defaultVal == $optValue[$index]) {
                        $str .= '<option  value="' . $optValue[$index] . '" selected>' . $val . '</option>';
                    } else {
                        $str .= '<option value="' . $optValue[$index] . '">' . $val . '</option>';
                    }
                }
                $str .= '</select>
                                                    </td>';
            } elseif ($value['varType'] == 5) {
                $str .= '<td>
                                                        &nbsp;&nbsp;&nbsp;&nbsp;<input type="file" class="mylabelValue" name="' . $varName . '_profile_' . $varType . '" id="' . $varName . '" >
                                                    </td>';
            } elseif ($value['varType'] == 6) {
                $str .= '<td>
                                                        &nbsp;&nbsp;&nbsp;&nbsp;<textarea rows="5" cols="50" name="' . $varName . '_profile_' . $varType . '" id="' . $varName . '" >' . $defaultVal . '</textarea>
                                                    </td>';
            } elseif ($value['varType'] == 9) {
                $str .= '<td>
                                                        &nbsp;&nbsp;&nbsp;&nbsp;<input type="password" class="mylabelValue" name="' . $varName . '_profile_' . $varType . '" id="' . $varName . '" value="' . $defaultVal . '">
                                                    </td>';
            }
            $str .= ' </tr>';
        }
        $str .= '</tbody>
                                        </table>
                                    </div>
                                </form><tr>
                                            <td>
                                                <input type="button" class="swal2-confirm btn btn-success btn-sm rightBtn" id="command_submit" value="Execute" onclick="' . $function . '">
                                            </td>
                                        </tr>';
    }
    echo $str . '##' . $scripName;
}
