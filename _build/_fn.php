<?php
function open_dir($dir) {
    $out = [];
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                $out[] = $file;
            }
        }
        closedir($handle);
    }
    return $out;
}

include_once '../cron/_fn.php';