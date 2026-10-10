<?php
$content = file_get_contents('resources/views/ocean-export/index.blade.php');
preg_match('/function oceanExportModule\(\) \{.*?\n\s{8}\}/s', $content, $matches);
if (empty($matches)) {
    echo "Could not find oceanExportModule\n";
    exit;
}
$js = $matches[0];
// replace blade tags with valid JS
$js = preg_replace('/\{\{.*?\}\}/', '1', $js);
$js = preg_replace('/@json\(.*?\)/', '{}', $js);
$js = preg_replace('/@if.*?@endif/s', '', $js);
$js = preg_replace('/<\?php.*?\?>/s', '', $js);
file_put_contents('temp.js', $js);
