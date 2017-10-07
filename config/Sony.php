<?php

return [
    'parser' => [
        'name'          => 'Sony',
        'enabled'       => true,
        'sender_map'    => [
            '/no-reply@snei.sony.com/',
        ],
        'body_map'      => [
            //
        ],
        'abuse-text' => "                                                                                                                                                                                        
                Pursuant to Sony Network Entertainment International LLC (SNEI) corporate policy, the below IP addresses were blacklisted from using our services because SNEI detected activity that is abusive to our network services. In our determination, the abusive activity was not related to velocity or volume, but matched the specific patterns of known abuse of our publicly available services. This abuse may be the result of a computer on your network that has been compromised and is participating in a botnet abuse of our services.

The following table of IP addresses, dates and times should help you correlate the origin of the abusive activity.  The time stamps are approximate from our logs.  The actual timing of the events depend on the signature matched.  It is very likely to have occurred both before, during and following the times listed.

It is most likely the attack traffic is directed at one of the following endpoints:

account.sonyentertainmentnetwork.com
auth.np.ac.playstation.net
auth.api.sonyentertainmentnetwork.com
auth.api.np.ac.playstation.net

These endpoints on our network are resolved by Geo DNS, so the IP addresses they resolve to will depend on the originating IP address.

The destination port will be TCP 443.

Please take the necessary measures to correct the malicious activity from the above-listed IP addresses as soon as possible to avoid any further disruptions. If we were to remove any of these IP addresses from the blacklist and subsequent abusive activity is detected, the IP address will be promptly blacklisted again. 
    ",
    ],

    'feeds' => [
        'abuse' => [
            'class'     => 'LOGIN_ATTACK',
            'type'      => 'ABUSE',
            'enabled'   => true,
            'fields'    => [
                'Source-IP',
                'Abuse-Date',
            ],
        ],
    ],
];
