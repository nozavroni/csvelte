<?php
trait TestFiles
{
    public function testfile($filename = null)
    {
        if (strpos($filename, DIRECTORY_SEPARATOR) === 0) {
            $filepath = realpath($filename);
        } else {
            $testdir = realpath(__DIR__ . '/../files');
            $filepath = realpath($testdir . DIRECTORY_SEPARATOR . ltrim($filename, DIRECTORY_SEPARATOR));
        }
        if ($filepath) {
            return $filepath;
        }
        throw new \Exception("Could not find test file: " . $filename);
    }
}
