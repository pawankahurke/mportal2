<?php
include_once '../../config.php';
include_once '../../lib/l-dbConnect.php';

if (!nhUser::isAuth()){
  echo 'not auth';
  exit();
}

$file = $_FILES['file']['tmp_name'];

if (($handle = fopen($file, 'r')) !== false) {
  $pdo = pdo_connect();
  while (($data = fgetcsv($handle, 1000, ',')) !== false) {
    if ($data[0] != 'Site Name') {
      //$data 0 - siteName
     //$data 1 - machineName

      $sitename = $data[0];
      $machine = $data[1];

      // Delete from Asset
      $asset_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'asset.Machine where host = ? and cust = ?');
      $asset_stmt->execute([$machine, $sitename]);

      $asst_stmt = $pdo->prepare('select machineid from ' . $GLOBALS['PREFIX'] . 'asset.Machine where host = ? and cust = ?');
      $asst_stmt->execute([$machine, $sitename]);
      $asst_data = $asst_stmt->fetch(PDO::FETCH_ASSOC);

      $machineid = $asst_data['machineid']; //error

      $asset_stmt2 = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'asset.AssetData where machineid = ? ');
      $asset_stmt2->execute([$machineid]);

      // Delete from Events
      $asset_stmt = $pdo->prepare('delete from  ' . $GLOBALS['PREFIX'] . 'event.Events where machine = ? and customer = ?');
      $asset_stmt->execute([$machine, $sitename]);

      // Delete for MUM
      $mum_del_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'swupdate.UpdateMachines where machine = ? and sitename = ?;');
      $mum_del_stmt->execute([$machine, $sitename]);

      $cen_stmt = $pdo->prepare('select id, censusuniq from ' . $GLOBALS['PREFIX'] . 'core.Census where site = ? and host = ? limit 1');
      $cen_stmt->execute([$sitename, $machine]);
      $cen_data = $cen_stmt->fetch(PDO::FETCH_ASSOC);

      deleteDataFromRedis($machine);

      if ($cen_data) {

        $census_id = $cen_data['id'];
        $censusuniq = $cen_data['censusuniq'];

        // delete queries
        $mch_del_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'softinst.Machine where id = ?');
        $mch_del_stmt->execute([$census_id]);

        $pat_del_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'softinst.PatchStatus where id = ?');
        $pat_del_stmt->execute([$census_id]);

        // Machine Groups
        $mgrp_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'core.MachineGroupMap using ' . $GLOBALS['PREFIX'] . 'core.MachineGroupMap left join '
          . $GLOBALS['PREFIX'] . 'core.Census on (' . $GLOBALS['PREFIX'] . 'core.MachineGroupMap.censusuniq = ' . $GLOBALS['PREFIX'] . 'core.Census.censusuniq) where id = ?;');
        $mgrp_stmt->execute([$census_id]);

        // MachineGroups delete
        $mgdelete_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'core.MachineGroups where mgroupuniq = ?');
        $mgdelete_stmt->execute([$censusuniq]);

        // ValueMap Delete
        $valumap_stmt = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.ValueMap where censusuniq = ?");
        $valumap_stmt->execute([$censusuniq]);

        // Census
        $cen_del_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'core.Census where id = ?');
        $cen_del_stmt->execute([$census_id]);

        //Census Perm
        // $cenp_del_stmt = $pdo->prepare('delete from '.$GLOBALS['PREFIX'].'core.CensusPerm where id = ?');
        // $cenp_del_stmt->execute([$census_id]);

      }

    }
  }
  echo 'success';
}

function deleteDataFromRedis($machine)
{
  $ServiceTag = $machine;
  $redis = RedisLink::connect();
  $redis->select(0);
  $redis->del("$ServiceTag", 0, -1);
}
?>
