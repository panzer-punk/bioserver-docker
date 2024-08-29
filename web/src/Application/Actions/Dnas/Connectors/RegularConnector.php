<?php

declare(strict_types=1);

namespace App\Application\Actions\Dnas\Connectors;

use App\Domain\Dnas\Connector;
use App\Domain\Dnas\ConnectResults;
use App\Domain\Dnas\DnasPacketException;

final class RegularConnector implements Connector
{
    public function __construct(
        private string $basePath
    ) {
        
    }

    public function connect(string $packet): ConnectResults
    {
        $basePath = $this->basePath;
        $gameID   = substr($packet, 0x2c, 8);
        $qrytype  = substr($packet, 0, 4);
        $fname    = bin2hex($gameID)."_".bin2hex($qrytype);

        // step 0 - create the checksums and keys for the answer packet
        $chksum1  = sha1(substr($packet, 0x34, 0x100));
        $chksum2  = sha1(substr($packet, 0x48,  0xec));
        $fullkey  = substr($chksum2, 0, 0x14*2) . substr($chksum1, 0, 0x0c*2);
        $desKey1 = pack("H*", substr($fullkey,    0, 0x10));
        $desKey2 = pack("H*", substr($fullkey, 0x10, 0x10));
        $desKey3 = pack("H*", substr($fullkey, 0x20, 0x10));
        $xorSeed = pack("H*", substr($fullkey, 0x30, 0x10));

        // step 1 - prepare the answer
        if (file_exists("{$basePath}/packets/{$fname}")) {
            $packet = file_get_contents("{$basePath}/packets/{$fname}");
            // step 2 - encrypt with keyset from query packet
            $packet = $this->encrypt3n($packet, 0xc8, 0x20, $desKey1, $desKey2, $desKey3, $xorSeed);
        
            // step 3 - encrypt with envelope keyset
            $packet = $this->encrypt3n(
                $packet,
                0x28,
                0x120,
                pack("H*", "eb711416cb0ab016"),
                pack("H*", "ae190174b5ce6339"),
                pack("H*", "7b01b91880145e34"),
                pack("H*", "c510a6400a9b022f")
            );
        } else {
            $packet = file_get_contents("{$basePath}/error.raw");
        };

        if ($packet === false) {
            throw new DnasPacketException;
        }

        return new ConnectResults("image/gif", strlen($packet), $packet);
    }

    private function encrypt3n(
        $data,
        $offset,
        $length,
        $desKey1,
        $desKey2,
        $desKey3,
        $xorSeed
    ) {
        $key = $xorSeed;

        for($i = 0; $i < $length; $i = $i + 8) {
            $dat = substr($data, $offset + $i, 8);
            for($t = 0; $t < 8; $t++) {
            $dat[$t] = $dat[$t] ^ $key[$t];
        }
    
        $enc = substr(
            base64_decode(
                openssl_encrypt($dat, "des-ecb", $desKey1)
            ),
            0,
            8
        );
        $enc = openssl_decrypt(
            $enc, 
            "des-ecb", 
            $desKey2, 
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
        );
        $enc = substr(
            base64_decode(
                openssl_encrypt($enc, "des-ecb", $desKey3)
            ),
            0,
            8
        );
    
        for($t = 0; $t < 8; $t++) {
            $data[$offset + $i + $t] = $enc[$t];
        }
            $key = $enc;
        }
    
        return($data);
    }
}
