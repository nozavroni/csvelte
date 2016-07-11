<?php namespace CSVelte\Filter;

class EncodeQuotedSpecialChars extends \PHP_User_Filter
{
    function onCreate()
    {
        $this->processed = 0;
        return true;
    }

    function filter($in, $out, &$consumed, $closing)
    {
        // while ($bucket = stream_bucket_make_writeable($in)) {
        //     echo "- $closing -";
        //     print($bucket->data);
        //     $this->processed = $consumed += $bucket->datalen;
        //     stream_bucket_append($out, $bucket);
        // }
        // echo $this->processed;
        // return PSFS_PASS_ON;
    }
}
