<?php
$files = glob('/var/www/html/heladeria/app/Models/*.php');
foreach($files as $f) {
    $content = file_get_contents($f);
    if(strpos($content, 'protected  = [];') !== false) {
        $content = str_replace('protected  = [];', 'protected $guarded = [];', $content);
        file_put_contents($f, $content);
        echo "Fixed $f\n";
    }
}
echo "Done.\n";
