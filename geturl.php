<?php

use Reflex\SteamAuth\SteamAuth;

include 'vendor/autoload.php';

echo (new SteamAuth())->buildUrl('http://doesplay.dev:1111/test.php', 'http://doesplay.dev');