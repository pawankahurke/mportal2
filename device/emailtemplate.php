<?php
global $emailTemplate;
global $base_url;


$customerName = $_SESSION['searchValue'];
$sql = "select id, username, customer from " . $GLOBALS['PREFIX'] . "core.Customers where customer = ? order by id limit 1;";
$res = NanoDB::find_one($sql, null, [$customerName]);

$custid = $res['id'];
$brandingPath = 'cust_' . $customerName . '_' . $custid;

$emailTemplate = '<div class="table-responsive ps">
        <table class="emailBox" cellpadding="0" cellspacing="0" width="100%" style="width: 460px; height: 460px; margin: 5% auto; max-width: 100%; background: #ccc; font-family: Montserrat; text-align: center; display: block; padding: 5% 7% 0% 7%; ">
            <tbody><tr>
                <td style="height: auto; width: 100%; float: left; color: #1d253b; font-size: 12px; font-family: Montserrat; text-align: center; display: block;">
                    <table cellpadding="0" cellspacing="0" width="100%" align="center">
                        <tbody><tr>
                            <td style="height: 100%; width: 100%; float: left; color: #1d253b;">
                                <img src="' . $base_url . 'assets/img/boxTop.png" alt="" border="0" align="center" style="width: 100%;">
                            </td>
                        </tr>
                    </tbody></table>
                </td>

                <td style="height: auto; width: 100%; float: left; color: #1d253b; font-size: 12px; font-family: Montserrat; text-align: center; display: block;">
                    <table class="innerMailNew" cellpadding="0" cellspacing="0" width="100%" align="center">
                        <tbody><tr>
                            <td style="height: 100%; width: 85%; float: left; color: rgb(0, 0, 0); background: rgb(255, 255, 255); padding: 7% 7% 0%; display: block;">
                                <img id="emailLogoPath" src="' . $base_url . 'assets/img/logo.png" alt="" border="0" align="center">
                            </td>
                        </tr>

                        <tr>
                            <td style="font-family: sans-serif; height: 100%; width: 85%; float: left; color: rgb(0, 0, 0); background: rgb(255, 255, 255); padding: 7%; font-size: 1.0625rem; display: block;">
                                <span id="emailTitle" ng-bind="emailTitle" class="ng-binding">EMAILTITLE</span>
                            </td>
                        </tr>

                        <tr>
                            <td style="font-family: sans-serif; height: 100%; width: 85%; float: left; color: rgb(0, 0, 0); background: rgb(255, 255, 255); padding: 0% 7% 7%; font-size: 0.7rem; display: block;">
                                <span ng-bind="emailBody" class="ng-binding">EMAILBODY</span>
                            </td>
                        </tr>

                        <tr>
                            <td style="font-family: sans-serif; height: 100%; width: 85%; float: left; color: rgb(0, 0, 0); background: rgb(255, 255, 255); padding: 0% 7% 7%; font-size: 0.7rem; display: block;">
                                <a href="DOWNLOADBTNURL" style="text-decoration: none;background: rgb(250, 15, 75);color: rgb(255, 255, 255);padding: 11px 40px;border-radius: 0.4285rem;font-family: sans-serif;font-size: 0.875rem;font-weight: 600;">Download</a>
                            </td>
                        </tr>

                        <tr>
                            <td style="font-family: sans-serif; height: 100%; width: 85%; float: left; color: rgb(0, 0, 0); background: rgb(255, 255, 255); padding: 0% 7% 7%; font-size: 0.7rem; display: block;">
                                Or copy paste the link given below in a browser to start your download.
                            </td>
                        </tr>

                        <tr>
                            <td style="font-family: sans-serif; height: 100%; width: 85%; float: left; color: rgb(0, 0, 0); background: rgb(255, 255, 255); padding: 0% 7% 7%; font-size: 0.7rem; display: block;">
                                <span style="text-decoration: underline;">DOWNLOADURL</span>
                            </td>
                        </tr>
                    </tbody></table>
                </td>
            </tr>
        </tbody></table>
    </div>';
