<?php
namespace Auth\Controllers\Callbacks;

use \Models\Entities\User;
use \Models\Entities\Service;
use \Models\Authorization;
use \Auth\Views\View;

// Test link: http://auth.invitations.stoiss.net/callback/oauth2/vk?code=dcf35dba1c86fc44c6

/**
 * 
 * @todo Обязательно логин запрашивать!!!
 * 
 */

abstract class Callback
{
    
    const ERR_INTERNAL = 1;   // внутренняя ошибка (неинформативные для пользователя ошибки)
    const ERR_ACCESS   = 2;   // нет доступа (пользователь не дал, или у него нет доступа и т.д.)
    const ERR_CANCEL   = 3;   // отменил операцию (пользователь передумал)
    const ERR_CLIENT   = 4;   // ошибка на стороне пользователя (не включены куки и т.д.)
    
    
    
    protected $service;

    
    public function __construct($serviceKey)
    {
        session_start();
        // check session
        if ( empty($_SESSION['appId']) || empty($_SESSION['redirectUri']) )
        {
            $this->page1(self::ERR_CLIENT);
            return;
        }
        if ( !($this->service = Service::getByKey($serviceKey)) )
        {
            $this->error(Authorization::ERR_SERVER_ERROR);
            return;
        }
        if ( !$this->entry() )
        {
            return;
        }
        if ( ($userServiceKey = $this->getKey()) === false )
        {
            $this->error(Authorization::ERR_SERVER_ERROR);
            return;
        }
        if ( empty($_SESSION['branch']) || $_SESSION['branch'] == 1 )
        {
            $user = $this->branch1($userServiceKey);
        }
        else if ( $_SESSION['branch'] == 2 )
        {
            
        }
        if ( empty($user) )
        {
            return;
        }

        // generate hash and send cookie
        setcookie('userhash', $user->genHash(), time() + User::HASH_TTL, '/');
        
        // generate code and send back to return uri
        $this->success(Authorization::genCode($user));
        
        
        die('Das ist good!'); // здесь остановился

        
        /*if ( !($user = $this->save($userServiceKey)) )
        {
            $this->_error(self::ERR_INTERNAL);
            return;
        }*/
        
        // создаем и сохраняем access code
        // !!! на этом этапе должен быть $user
        //$code = \Models\Authorization::genCode();
        
        // здесь записываем в cookie
        // $_COOKIE['userhash'] = 
        
        // возвращаем клиенту код авторизации
        //$this->returnSuccess($code);
        
        
        /*do
        {
            $token = uniqid();
        } 
        while ( !$user->createSession($token, null) );
        $this->returnSuccess($token);*/
    }
    
    protected function page1($error)
    {
        $view = new View('ru_RU.UTF-8');
        switch ( $error )
        {
            case self::ERR_ACCESS:
            {
                $view->set('error', 'access');
                break;
            }
            case self::ERR_CANCEL:
            {
                $view->set('error', 'cancel');
                break;
            }
            case self::ERR_CLIENT:
            {
                $view->set('error', 'client');
                break;
            }
            default:
            {
                $view->set('error', 'internal');
                break;
            }
       }
        $view->show('error');
    }
    
    protected function page2($accounts)
    {
        $view = new View('ru_RU.UTF-8');
        $view->set('settings', Authorization::getSettings());
        $view->set('accounts', $accounts);
        $view->show('choice');
    }
    

    
    // если false, то ничгео не делать в вызывающей функции
    protected function branch1($userServiceKey)
    {
        $user = User::getByKey($userServiceKey);
        if ( empty($user->id) )
        {

            if ( $accounts = $this->findAccounts() )
            {
                $_SESSION['branch'] = 2;
                $this->page2($accounts);
                return false;
            }
            
            $user = new User;
            $user->bindService($userServiceKey, $this->service);
            $user->save();
        }
        return $user;
    }
    
    
    protected function branch2(User $user, $userServiceKey)
    {
        $user->bindService($userServiceKey, $user->service);
        $user->save();
    }
    
    private function findAccounts()
    {
        $conditions = array();
        $emails = array();
        $phones = array();
        if ( $v = $this->getEmail() )
        {
            $conditions[] = array('email' => $v);
        }
        if ( $v = $this->getEmailList() )
        {
            for ( $i=0; $i<count($v); $i++ )
            {
                $conditions[] = array('email' => $v[$i]);
            }
        }
        if ( $v = $this->getPhone() )
        {
            $conditions[] = array('phone' => $v);
        }
        if ( $v = $this->getPhoneList() )
        {
            for ( $i=0; $i<count($v); $i++ )
            {
                $conditions[] = array('phone' => $v[$i]);
            }
        }
        if ( empty($conditions) )
        {
            return false;
        }
        if ( !($users = User::findByProperties($conditions)) )
        {
            return false;
        }
        $j = 0;
        $res = array();
        for ( $i=0; $i<count($users); $i++ )
        {
            if ( $services = Service::getByUserId($users[$i]->id) )
            {
                $res[] = array
                (
                    'user'     => $users[$i],
                    'services' => $services
                );
            }
        }
        return $res;
    }
    
    
    protected function getKey()
    {
        if ( ($u = $this->getUserId()) !== false )
        {
            return sha1($this->service->key . '://' . $u);
        }
        else if ( ($u = $this->getEmail()) !== false )
        {
            return sha1($this->service->key . '://' . $u);
        }
        else if ( ($u = $this->getPhone()) !== false )
        {
            return sha1($this->service->key . '://' . $u);
        }
        return false;
    }
    
    
    /*protected function createAuthCode($userId)
    {
        $kvdb = KVDB::ns('auth:codes');
        do
        {
            $code = uniqid();
        }
        while ( $kvdb->get($code) );
        $kvdb->set($code, $userId);
        $kvdb->expire($code, 3600 * 5);
        return $code;
    }*/
    

    /*
     * 
     * false, если ошибка!!!
     */
    /*protected function save($userServiceKey)
    {
        $user = User::getByKey($userServiceKey);
        if ( isset($user->id) )
        {
            return $user;
        }
        $userId = 0;
        // тут ищем подходящего пользователя
        if ( !$userId )
        {
            $user = new User;
            $user->bindService($userServiceKey, $this->service);
            $user->save();
        }
        return $user;
    }*/
    
    /*protected function createUser()
    {
        
    }*/

    
    protected abstract function entry();
    protected abstract function getUserId();
    protected abstract function getEmail();
    protected abstract function getEmailList();
    protected abstract function getPhone();
    protected abstract function getPhoneList();

    
    
    protected function error($error, $description='')
    {
        $error = Authorization::getErrorAsString($error);
        if ( $_SESSION['redirectUri'] )
        {
            $p = parse_url($_SESSION['redirectUri']);
            $uri  = $p['scheme'] . '://' . $p['host'] . (empty($p['port'])? '': $p['port']) . $p['path'];
            $uri .= '?' . (($p['query'] == '')? '': ($p['query'] . '&')) . 'error=' . urlencode($error);
            if ( $description != '' )
            {
                $uri .= '&error_description=' . urlencode($description);
            }
            if ( $_SESSION['state'] != '' )
            {
                $uri .= '&state=' . $_SESSION['state'];
            }
            header("Location: {$uri}");
        }
        else
        {
            $data = array( 'error' => $error );
            if ( $description != '' )
            {
                $data['error_description'] = $description;
            }
            header($_SERVER["SERVER_PROTOCOL"] . ' 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode($data);
        }
    }
    
    
    protected function success($code)
    {
        $p = parse_url($_SESSION['redirectUri']);
        $uri  = $p['scheme'] . '://' . $p['host'] . (empty($p['port'])? '': $p['port']) . $p['path'];
        $uri .= '?' . (($p['query'] == '')? '': ($p['query'] . '&')) . 'code=' . urlencode($code);
        if ( isset($_SESSION['state']) && $_SESSION['state'] != '' )
        {
            $uri .= '&state=' . $_SESSION['state'];
        }
        header("Location: {$uri}");
    }

    
}
