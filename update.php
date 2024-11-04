<?php

function download_file($topic) {
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, "https://dsi.ut-capitole.fr/blacklists/download/$topic.tar.gz");
    $output_file = fopen("tmp/$topic.tar.gz", 'w');
    curl_setopt($curl_handle, CURLOPT_FILE, $output_file);
    curl_exec($curl_handle);
    curl_close($curl_handle);
    fclose($output_file);
}

function unzip_file($topic) {
    $archive = new PharData("tmp/$topic.tar.gz");
    $archive->extractTo('tmp/extract');
}

function translate($topic) {
    if (file_exists("tmp/extract/$topic/domains")) {
        if (file_exists("output/$topic")) {
            unlink("output/$topic");
        }
        $output_file = fopen("output/$topic", 'a');
        foreach (file("tmp/extract/$topic/domains") as $domain) {
            fwrite($output_file, "||".trim($domain)."^".PHP_EOL);
        }
        fclose($output_file);
    }
}

function cleanup ($topic) {
    if (file_exists("tmp/extract/$topic")) {
        array_map('unlink', glob("tmp/extract/$topic/*"));
        rmdir("tmp/extract/$topic");
    }
}

function download_and_translate($topic) {
    if (file_exists("tmp/$topic.tar.gz")) {
        if (filemtime("tmp/$topic.tar.gz") + (60 * 60 * 12) > time()) {
            echo $topic." updated less than 12 hours ago";
            return;
        }
    }
    download_file($topic);
    unzip_file($topic);
    translate($topic);
    cleanup($topic);
}

$topics = ['games'];

if (!file_exists('tmp')) {
    mkdir('tmp', 0777, true);
}
if (!file_exists('tmp/extract')) {
    mkdir('tmp/extract', 0777, true);
}
if (!file_exists('output')) {
    mkdir('output', 0777, true);
}

foreach ($topics as $topic) {
    download_and_translate($topic);
}

