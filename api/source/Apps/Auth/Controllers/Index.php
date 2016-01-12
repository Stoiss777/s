<?php
namespace Auth\Controllers;

use \Auth\Views\View;
use \Models\Entities\App;
use \Models\Entities\User;
use \Models\Authorization;

/**
 * 
 * Ссылка для проверки: http://auth.invitations.stoiss.net/?redirect_uri=http%3A%2F%2Fstoiss.net&response_type=code&client_id=2&state=xyz
 * 
 */

class Index extends Controller
{
    
    protected $app;
    protected $redirectUri;
    protected $scope;
    protected $state;
    
    
    public function __construct()
    {
        // Check redirect uri
        if ( !isset($_GET['redirect_uri']) )
        {
            $this->error(Authorization::ERR_INVALID_REQUEST, 'redirect_uri is missing');
            return false;
        }
        $redirectUri = urldecode($_GET['redirect_uri']);
        
        // Check client id
        if ( isset($_GET['client_id']) )
        {
            if ( !($this->app = App::getById(intval($_GET['client_id']))) )
            {
                $this->error(Authorization::ERR_INVALID_CLIENT);
                return;
            }
        }
        else
        {
            $this->error(Authorization::ERR_INVALID_REQUEST, 'client_id is missing');
            return;
        }
        
        // Enabled?
        if ( !$this->app->enabled )
        {
            $this->error(Authorization::ERR_UNATHORIZED_CLIENT);
            return;
        }
        
        // Check that redirect uri is available
        if ( !($redirectHost = parse_url($redirectUri, PHP_URL_HOST)) || !in_array($redirectHost, $this->app->domains) )
        {
            $this->error(Authorization::ERR_INVALID_REQUEST, 'redirect_uri has wrong domain');
            return;
        }
        $this->redirectUri = $redirectUri;
        
        // state
        $this->state = isset($_GET['state'])? $_GET['state']: '';

        
        // Check scopes
        if ( $this->app->trusted && !empty($this->app->scopes) )
        {
            $this->scopes = $this->app->scopes;
        }
        else if ( isset($_GET['scope']) && !empty($this->app->scopes) )
        {
            $this->scopes = explode(',', $_GET['scope']);
            foreach ( $this->scopes as $scope )
            {
                if ( !in_array($scope, $this->app->scopes) )
                {
                    $this->error(Authorization::ERR_INVALID_SCOPE);
                    return;
                }
            }
        }
        else
        {
            $this->error(Authorization::ERR_INVALID_REQUEST, 'scope is missing');
            return false;
        }
        
        // response type must be "code"
        if ( !isset($_GET['response_type']) || ($_GET['response_type'] != 'code') )
        {
            $this->error(Authorization::ERR_UNSUPPORTED_RESPONSE_TYPE, 'response_type has wrong type');
            return;
        }
        
        
        // save all it to session
        session_start();
        $_SESSION['appId']       = $this->app->id;
        $_SESSION['redirectUri'] = $this->redirectUri;
        $_SESSION['scope']       = $this->scopes;
        $_SESSION['state']       = $this->state;
        
        // maybe the user already authorized
        // temporary off
        /*if ( !empty($_COOKIE['userhash']) && ($user = User::getByHash($_COOKIE['userhash'])) ) 
        {
            // refresh cookie
            setcookie('userhash', $_COOKIE['userhash'], time() + User::HASH_TTL, '/');
            // generate code and returning
            $this->success($redirectUri, Authorization::genCode($user));
            return;
        }*/
        
        // view
        $view = new View('ru_RU.UTF-8');
        $lib  = new Authorization;
        $view->set('settings', $lib->getSettings()); // @todo static???
        $view->show('index');
    }
    
    
    protected function success($code)
    {
        $p = parse_url($this->redirectUri);
        $uri  = $p['scheme'] . '://' . $p['host'] . (empty($p['port'])? '': $p['port']) . $p['path'];
        $uri .= '?' . (($p['query'] == '')? '': ($p['query'] . '&')) . 'code=' . urlencode($code);
        if ( $this->state != '' )
        {
            $uri .= '&state=' . $this->state;
        }
        header("Location: {$uri}");
    }
    
    
    protected function error($error, $description='')
    {
        $error = Authorization::getErrorAsString($error);
        if ( $this->redirectUri )
        {
            $p = parse_url($this->redirectUri);
            $uri  = $p['scheme'] . '://' . $p['host'] . (empty($p['port'])? '': $p['port']) . $p['path'];
            $uri .= '?' . (($p['query'] == '')? '': ($p['query'] . '&')) . 'error=' . urlencode($error);
            if ( $description != '' )
            {
                $uri .= '&error_description=' . urlencode($description);
            }
            if ( $this->state != '' )
            {
                $uri .= '&state=' . $this->state;
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

    
}
