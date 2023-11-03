<?php



function convertTimeFromTimezone($fromTimezone, $toTimezone, $time, $timeFormat = 'm/d/Y H:i:s' , $level = '')
{
  if ($time) {
    if ($level == 'login') {
      $date = new DateTime(date($timeFormat, $time), new DateTimeZone($fromTimezone));
    } else {
      $date = new DateTime(date(DateTimeInterface::ATOM, $time), new DateTimeZone($fromTimezone));
    }

    $date->setTimezone(new DateTimeZone($toTimezone));
    return $date->format($timeFormat);
  }
}


?>
