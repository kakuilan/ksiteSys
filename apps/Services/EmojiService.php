<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/4/11
 * Time: 19:54
 * Desc: Emoji表情服务类
 */

namespace Apps\Services;

use Emojione\RulesetInterface;
use Emojione\ClientInterface;
use Emojione\Client;
use Emojione\Ruleset;
use Emojione\Emojione;

class EmojiService extends ServiceBase {

    public static function getClient() {
        static $client;
        if(is_null($client)) {
            $client = new Client(new Ruleset());
        }

        return $client;
    }


    public static function asciiToShortname($string='') {
        if(empty($string)) return '';
        return self::getClient()->asciiToShortname($string);
    }


    public static function shortnameToAscii($string='') {
        if(empty($string)) return '';
        return self::getClient()->shortnameToAscii($string);
    }


    public static function unicodeToShortname($string='') {
        if(empty($string)) return '';
        return self::getClient()->toShort($string);
    }

    public static function shortnameToUnicode($string='') {
        if(empty($string)) return '';
        return self::getClient()->shortnameToUnicode($string);
    }

    public static function shortnameToImage($string='') {
        if(empty($string)) return '';
        return self::getClient()->shortnameToImage($string);
    }


    /**
     * 替换表情shortname
     * @param string $string
     * @param string $new
     * @return null|string|string[]
     */
    public static function replaceShortname($string='', $new='') {
        if(empty($string)) return '';
        $string = preg_replace('/:[a-z]+:/i', $new, $string);
        return $string;
    }


}