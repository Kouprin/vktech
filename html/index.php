<?php

// check flood sessions
// check flood queries

require_once "./php/html_builder.php";

$html = htmlBuildPage(htmlBuildMeta(), htmlBuildBody());
print($html);
