<?php

include "internal.php";

header("Content-Type: application/json");
// Check Key
if (!checkKey($_REQUEST["key"])) {
    exit(errorJson("The key is wrong!"));
}
// Process argument
$id = $_REQUEST["id"];
if ($id == null) {

}