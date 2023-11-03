<?php

echo "Start recalculating tokens...<br>";
flush();

define('TokenChecker_no_die', true); // This is for the TokenChecker class to not die if db is corrupted.


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

// This code calculates the token for all the places in the database.
TokenManager::calcTokens();

echo "Done";
