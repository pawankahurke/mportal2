<?php


/**
 * Class Options for table core.Options
 */
class Options
{
  protected function __construct()
  {
  }

  /**
   * @param $name
   * @return array|null
   */
  public static function getOption($name){
    $sql = "select * from\n"
      . " ".$GLOBALS['PREFIX']."core.Options\n"
      . "WHERE name = '".$name."'";

    $result  = NanoDB::find_one($sql);
    return $result;
  }

  /**
   * @param $name
   * @param $value
   */
  public static function setOption($name,$value){
    $sql = "update ".$GLOBALS['PREFIX']."core.Options\n"
      ."SET value = '".$value."' WHERE name = '".$name."'";

    NanoDB::query($sql);
  }

}
?>
