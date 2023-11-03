<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
require_once 'l-db.php';

$pdo = pdo_connect();

$mailTemplate= '<body style="background: #ccc;">
    <table cellpadding="0" cellspacing="0" width="100%" style="width: 650px; margin: auto;">
        <tr>
            <td align="center" style="background: #fff; padding: 0; font-family: \'Helvetica\', Arial, sans-serif; font-weight: 400;">
                <table cellpadding="0" cellspacing="0" width="650" align="center">
                    <tr>
                        <td align="center" style="width: 650px; font-size: 30px; font-family: \'Helvetica\', Arial, sans-serif; color: #666666; margin: auto;">
                            <table cellpadding="0" cellspacing="0" width="100%" align="center">
                                <tr>
                                    <td align="center" style="width: 530px; font-size: 30px; font-family: \'Helvetica\', Arial, sans-serif; color: #666666; float: left; margin: 66px auto 0px; padding: 0px 60px; line-height: 17px; height: 100px;">
                                        <table cellpadding="0" cellspacing="0" width="100%" align="center">
                                            <tr>
                                                <td align="left" style="font-size: 30px; font-family: \'Helvetica\', Arial, sans-serif; float: left; color: #666666;">
                                                    <a href="javascript:void(0);" target="_blank"><img src="DASHBOARDLOGO" alt="" border="0" align="left" /></a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="width: 650px; height: auto; font-size: 44px; font-family: \'Helvetica Bold\', Arial, sans-serif; color: #666666; float: left; margin: 0px auto 10px; font-weight: bolder; line-height: 50px;">
                                        <table cellpadding="0" cellspacing="0" width="100%" align="center">
                                            <tr>
                                                <td style="width: 650px; height: auto; float: left; text-align: center; color: #081c2b; font-size: 44px; font-family: \'Helvetica Bold\', Arial, sans-serif; font-weight: bolder; line-height: 50px;" width="100%" align="center">
                                                     You&apos;re just one step away  <br/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="width: 650px; height: auto; float: left; text-align: center; color: #666666; font-size: 16px; font-family: \'Helvetica\', Arial, sans-serif; font-weight: normal; line-height: 22px; padding-bottom:15px; margin-top: 24px;" width="100%" align="center">
                                                    Set your login password now  <br/> 
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                            
                                <tr>
                                    <td align="center" style="width: 650px; height: auto; font-size: 44px; font-family: \'Helvetica Bold\', Arial, sans-serif; color: #666666; float: left; padding-bottom: 30px; margin: 10px auto -25px; font-weight: bolder;;">
                                        <table align="center" class="centerCol">
                                            <tr>
                                                <td class="centerCol">

                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td>
                                                                
                                                                    <tr>
                                                                        <td align="center">
                                                                            <div>
                                                                                <!--[if mso]>
  <v:rect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="PASSURL" style="height:40px;v-text-anchor:middle;width:250px;" stroke="f" fillcolor="#FD0047">
    <w:anchorlock/>
    <center>
  <![endif]-->
                                                                                <a href="PASSURL" style="background-color:#FD0047;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:17px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:250px;-webkit-text-size-adjust:none;">Set Password Now</a>
                                                                                <!--[if mso]>
    </center>
  </v:rect>
<![endif]-->
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                

                                 <tr>
                                    <td align="center" style="width: 650px; height: auto; font-size: 14px; font-family: \'Helvetica\', Arial, sans-serif; color: #666666; float: left; margin: 0px auto 20px;">
                                        <table cellpadding="0" cellspacing="0" width="100%" align="center"> <br> <br>
                                            <tr>
                                                <td style="width: 650px; height: auto; float: left; text-align: center; color: #081c2b; font-size: 14px; font-family: \'Helvetica\', Arial, sans-serif;" width="100%" align="center">
                                                    We look forward to serving you.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                    
                     
                                <tr>
                                    <td align="center" style="width: 530px; font-size: 16px; font-family: \'Helvetica\', Arial, sans-serif; color: #666666; margin: 30px 60px 50px; line-height: 24px; height: auto;">
                                        <table cellpadding="0" cellspacing="0" width="100%" align="center">
                                            <tr>
                                                <td style="width: 100px; height: auto; float: left; text-align: left; color: #666666; font-size: 11px; font-family: \'Helvetica\', Arial, sans-serif; margin: 30px 0px 50px 60px;">
                                                    <h3>DASHBOARDNAME</h3>
                                                </td>

                                                <td style="width: 140px; height: auto; float: center; text-align: center; color: #fc3f1d; font-size: 11px; font-family: \'Helvetica\', Arial, sans-serif; margin: 30px 10px 50px 10px;">
                                                    Automated issue resolution.
                                                </td>

                                                <td style="width: 100px; height: auto; float: right; text-align: right; color: #666666; font-size: 11px; font-family: \'Helvetica\', Arial, sans-serif; margin: 30px 60px 50px 0px;">
                                                   CUSTOMERCONTACTNO
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="width: 650px; height: auto; font-size: 11px; font-family: \'Helvetica\', Arial, sans-serif; color: #666666; float: left; margin: 0px auto 57px; line-height: 15px;">
                                        <table cellpadding="0" cellspacing="0" width="100%" align="center">
                                            <tr>
                                                <td style="width: 650px; height: auto; float: left; text-align: center; color: #666666; font-size: 11px; font-family: \'Helvetica\', Arial, sans-serif; line-height: 15px; margin: 0px auto 57px;" width="100%" align="center">
                                                    This email was sent to you because you subscribed to our newsletters or signed up for a membership.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>';

$stmt = $pdo->prepare("update ".$GLOBALS['PREFIX']."agent.emailTemplate set  mailTemplate = ? where ctype = ? and country = ? and language = ?");
$datares = $stmt->execute([$mailTemplate, 10, 'USA', 'en']);

if($datares) {
    echo 'Template Updated Successfully!';
} else {
    echo 'failed to update mail template!';
}

?>