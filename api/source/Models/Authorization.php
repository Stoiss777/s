<?php
namespace Models;

use \Libs\KVDB\KVDB;
use \Builders\Libs\OAuth2;
use \Models\Entities\User;


class Authorization 
{
 
    
    const CODE_SOLT = '3_9kxWd9aZx';
    const CODE_TTL  = 180; 
    
    const ERR_SERVER_ERROR = 2;
    const ERR_TEMORARY_UNAVAILABLE = 3;
    const ERR_INVALID_REQUEST = 5;
    const ERR_INVALID_CLIENT = 6;
    const ERR_INVALID_GRANT = 7;
    const ERR_INVALID_SCOPE = 8;
    const ERR_ACCESS_DENIED = 9;
    const ERR_UNATHORIZED_CLIENT = 10;
    const ERR_UNSUPPORTED_GRANT_TYPE = 11;
    const ERR_UNSUPPORTED_RESPONSE_TYPE = 12;
    
    
    public static function getErrorAsString($error)
    {
        switch ($error)
        {
            case self::ERR_TEMORARY_UNAVAILABLE:
            {
                return 'temporary_unavailable';
            }
            case self::ERR_INVALID_REQUEST:
            {
                return 'invalid_request';
            }
            case self::ERR_INVALID_CLIENT:
            {
                return 'invalid_client';
            }
            case self::ERR_INVALID_GRANT:
            {
                return 'invalid_grant';
            }
            case self::ERR_INVALID_SCOPE:
            {
                return 'invalid_scope';
            }
            case self::ERR_ACCESS_DENIED:
            {
                return 'access_denied';
            }
            case self::ERR_UNATHORIZED_CLIENT:
            {
                return 'unathorized_client';
            }
            case self::ERR_UNSUPPORTED_GRANT_TYPE:
            {
                return 'unsupported_grant_type';
            }
            case self::ERR_UNSUPPORTED_RESPONSE_TYPE:
            {
                return 'unsupported_response_type';
            }
            default:
            {
                return 'server_error';
            }
        }
    }
    
    
    public static function genCode(User $user)
    {
        $kvdb = new KVDB('auth:codes');
        do
        {
            $code = sha1($user->id . ':' . self::CODE_SOLT . ':' . uniqid());
        }
        while ( $kvdb->get($code) );
        $kvdb->set($code, $user->id);
        $kvdb->expire($code, self::CODE_TTL);
        return $code;
    }
    
    public static function getSettings()
    {
        $kvdb = new KVDB('auth');
        if ( !isset($kvdb->settings) )
        {
            $kvdb->settings = array
            (
                'vk.uri' => OAuth2::getByKey('vk')->getCodeUri(),
                'ok.uri' => OAuth2::getByKey('ok')->getCodeUri()
            );
        }
        return $kvdb->settings;
    }
    

    
}
