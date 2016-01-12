<?php
namespace Builders\Libs;

use \Models\Settings;
use \Libs\OAuth2\OAuth2 as OAuth2Lib;


class OAuth2
{
    
    
    public static function getById($id)
    {
        $object = OAuth2Lib::getInstance($id);
        if ( $object instanceof OAuth2Lib )
        {
            $key = 'services.' . OAuth2Lib::$classes[$object->getService()]['key'] . '.auth';
            $settings = Settings::access('private')->get("{$key}.*");
            $object->setRedirectUri($settings["{$key}.redirectUri"]);
            $object->setClientId($settings["{$key}.clientId"]);
            $object->setClientSecret($settings["{$key}.clientSecret"]);
            $object->setScope($settings["{$key}.scope"]);
            $object->setState($settings["{$key}.state"]);
            $object->setCodeUrl($settings["{$key}.codeUrl"]);
            $object->setTokenUrl($settings["{$key}.tokenUrl"]);
            return $object;
        }
        return false;
    }
    
    
    public static function getByKey($key)
    {
        if ( !($id = OAuth2Lib::getServiceByKey($key)) )
        {
            return false;
        }
        return self::getById($id);
    }
    
    
}
