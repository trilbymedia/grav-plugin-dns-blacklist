<?php

namespace Grav\Plugin\DNSBlacklist;

use Grav\Common\Grav;
use Grav\Common\Uri;

class Blacklist
{
    public function isBlacklisted($ip = null)
    {
        $dnsbl_lookup = $this->getDNSBLs();
        $ip = $ip ?? Uri::ip();
        $listed = [];

        if ($ip) {
            $reverse_ip = implode(".", array_reverse(explode(".", $ip)));
            foreach ($dnsbl_lookup as $host) {
                if (checkdnsrr($reverse_ip . "." . $host . ".", "A")) {
                    $listed[] = $host;
                }
            }
        }
        return $listed;
    }

    protected function getDNSBLs()
    {
        return Grav::instance()['config']->get('plugins.dns-blacklist.list', []);
    }
}