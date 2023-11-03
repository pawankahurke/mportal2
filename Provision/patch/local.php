<?php

/*
Revision history:

Date        Who     What
----        ---     ----
29-Oct-03   EWB     Created.
 9-Dec-03   EWB     Added "Special" variable names.
11-Dec-03   EWB     Renamed "special" variable names.
17-Dec-03   EWB     Don't need submenu any more.
29-Dec-03   EWB     Added constVendorUser
 9-Aug-03   EWB     the return of patch_navigate.
26-Aug-06   BTE     Added status_opt from wu-stats.php.

*/

function patch_navigate($comp)
{
    $dir = $comp['odir'];
    $p   = "/$dir/patch";
    $a   = array();
    $cfg = "$p/wu-confg.php?act=menu";
    $sit = "$p/wu-sites.php";
    $upd = "$p/wu-patch.php";
    $a[] = html_link($sit, 'sites');
    $a[] = html_link($upd, 'updates');
    $a[] = html_link($cfg, 'config');
    $m   = join(' | ', $a);
    return "$m\n<br><br>\n";
}

function green($msg)
{
    return "<font color=\"green\">$msg</font>";
}

function debug_array($debug, $p)
{
    if ($debug) {
        reset($p);
        foreach ($p as $key => $data) {
            $msg = green("$key: $data");
            echo "$msg<br>\n";
        }
    }
}

function two_col($prompt, $action)
{
    return <<< HERE
<tr>
    <td align="right">
        $prompt
    </td>
    <td align="left">
        $action
    </td>
</tr>

HERE;
}


function status_opt()
{
    return array(
        -1 => constTagNone,
        constPatchStatusInvalid => constTagAny,
        constPatchStatusNotHandledOnServer => 'automatic',
        constPatchStatusPendingImmediateInstall => 'pending install',
        constPatchStatusPendingImmediateUninstall => 'pending uninstall',
        constPatchStatusPendingScheduledInstall => 'scheduled install',
        constPatchStatusPendingScheduledUninstall => 'scheduled uninistall',
        constPatchStatusInstallDisabled => 'disabled',
        constPatchStatusInstallFailed => 'error',
        constPatchStatusInstalled => 'installed',
        constPatchStatusUninstalled => 'uninstalled',
        constPatchStatusDownloaded => 'downloaded',
        constPatchStatusDetected => 'detected',
        constPatchStatusPendingDownload => 'pending download',
        constPatchStatusPendingReboot => 'needs reboot',
        constPatchStatusPotentialFailure => 'potential installation failure',
        constPatchStatusSuperseded => 'superseded',
        constPatchStatusWaiting => 'waiting'
    );
}
