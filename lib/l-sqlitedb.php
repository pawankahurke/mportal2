<?php


class MyDB extends SQLite3
   {
      function __construct()
      {
        $this->open("../Querydb/profile_'".time()."'.db");
      }
   }

class MyDBUpload extends SQLite3 {

    function __construct($path) {
        $this->path = $path;
        $this->open($this->path . "/profile_" . time() . ".db");
    }

}

class SQLDBFirstCustomer extends SQLite3 {

    function __construct($path) {
        $this->path = $path;
        $this->open($this->path . "/profile_" . time() . ".db");
    }
}
