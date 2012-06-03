<?php

$token = 'XDEBUG_PROFILE';

if (!isset($_COOKIE[$token])) {
    setCookie('XDEBUG_PROFILE', '1', time() + 3600);
    echo 'Profiler enabled.';
} else {
    setCookie($token, '', 1000);
    echo 'Profiler disabled.';
}
?>