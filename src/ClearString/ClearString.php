<?php
/**
 * Class for clearing unsafe chars in string
 * 
 * @package ClearString
 * @category Utils
 * @author Desin Savin <denis.savin@hfn.local>
 * @example ./src/ClearString/ClearString.php:57
 */
class ClearString
{
   /**
   * Clear string by chars which may cause SQL injection
   *
   * @param string $unescaped_string - unsafe string
   * @return string clear string
   */
  public function  clear_by_sql_injection($unescaped_string){
    $replacementMap = [
      "\0" => "\\0",
      "\n" => "\\n",
      "\r" => "\\r",
      "\t" => "\\t",
      chr(26) => "\\Z",
      chr(8) => "\\b",
      '"' => '\"',
      "'" => "\'",
      '_' => "\_",
      "%" => "\%",
      '\\' => '\\\\'
    ];

    return \strtr($unescaped_string, $replacementMap);
  }

  /**
   * Clear string by chars which may cause XSS
   *
   * @param string $unescaped_string - unsafe string
   * @return string clear string
   */
  public function clear_by_xss($unescaped_string){
    return htmlspecialchars($unescaped_string, ENT_QUOTES, 'UTF-8');;
  }

  /**
   * Clear string by chars which may cause SQL injection and XSS
   *
   * @param string $unescaped_string - unsafe string
   * @return string clear string
   */
  public function clear_by_all($unescaped_string){
    return $this->clear_by_xss($this->clear_by_sql_injection($unescaped_string));
  }
}

// $CS = new ClearString();

// echo $CS->clear_by_all("OR+''+==+''+<script>123;++\'\'</script>");
// output: OR+\&#039;\&#039;+==+\&#039;\&#039;+&lt;script&gt;123;++\\\&#039;\\\&#039;&lt;/script&gt;

// echo $CS->clear_by_all("{xxx:\"yyy\"}");
// output: {xxx:\&quot;yyy\&quot;}


// $CS->clear_by_all(2)