<?php
$directory = 'E:/data/thiurkural'; // Replace with the path to your folder
$files = array();

// Open the directory and read its contents
if (is_dir($directory)) {
    if ($dh = opendir($directory)) {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                $files[] = $directory . '/' . $file; // Add the file path to the array
            }
        }
        closedir($dh);
    }
}

// Return the list of files as JSON
echo json_encode($files);
?>
