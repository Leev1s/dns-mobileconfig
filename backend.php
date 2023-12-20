<?php
/**
 * Sign MobileConfig
 *
 * @string $file_full_pathname   e.g. /tmp/example.mobileconfig
 * @string $certificate_pathname
 * @string $private_key_pathname
 * @string $chain_pathname
 * @bool   $replace_file          Optional, default is true, if you want to keep your unsigned file then set to false.
 *
 * @return string
 */
function signMobileConfig (
    string $file_full_pathname,
    string $certificate_pathname,
    string $private_key_pathname,
    string $chain_pathname,
    bool $replace_file = true
): void {
    $signed_name = basename($file_full_pathname, ".mobileconfig") . "_signed.mobileconfig";
    $base_directory = dirname($file_full_pathname);
    $signed_path = $base_directory . DIRECTORY_SEPARATOR . $signed_name;

    exec("openssl smime -sign -signer {$certificate_pathname} -inkey {$private_key_pathname} -certfile {$chain_pathname} -nodetach -outform der -in {$file_full_pathname} -out {$signed_path}");
    if($replace_file) {
        rename($signed_path, $file_full_pathname);
    }
}

if($_SERVER['REQUEST_METHOD']=='POST') {
    $filename = uniqid() . ".mobileconfig";
    $filepath = __DIR__ . "/files/" . $filename;
    file_put_contents($filepath, file_get_contents($_FILES['data']["tmp_name"]));

    $sign = filter_var($_POST['sign'], FILTER_VALIDATE_BOOLEAN);
    if ($sign == true) {
        //On the webserver, this is where the certificates are.
        signMobileConfig($filepath, "../../cert/config.notjakob.com.crt", "../../cert/config.notjakob.com.key", "../../cert/chain.crt");
    }
    echo $filename;
}
else {
    echo "You found the backend script, good job.";
}
?>
