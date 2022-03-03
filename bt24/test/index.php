<?PHP
function getFileList($dir)
{
    // array to hold return value
    $retval = [];

    // add trailing slash if missing
    if (substr($dir, -1) != "/") {
        $dir .= "/";
    }

    // open pointer to directory and read list of files
    $d = @dir($dir) or die("getFileList: Failed opening directory {$dir} for reading");
    while (FALSE !== ($entry = $d->read())) {
        // skip hidden files
        if ($entry{
        0} == ".") continue;
        if (is_dir("{$dir}{$entry}")) {
            $retval[] = [
                'name' => "{$entry}/",
                'type' => filetype("{$dir}{$entry}"),
                'size' => 0,
                'lastmod' => filemtime("{$dir}{$entry}")
            ];
        } elseif (is_readable("{$dir}{$entry}")) {
            $retval[] = [
                'name' => "{$entry}",
                'type' => mime_content_type("{$dir}{$entry}"),
                'size' => filesize("{$dir}{$entry}"),
                'lastmod' => filemtime("{$dir}{$entry}")
            ];
        }
    }
    $d->close();

    return $retval;
}

$files = getFileList(__DIR__);

foreach ($files as $key => $file) {
    if ($file['type'] == 'text/plain') {
        echo "<a target='_blank' href='read.php?f=$file[name]'>" . date('Y-m-d H:i:s', $file['lastmod']) . "</a><br>";
    }
}
