<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/28
 * Time: 15:27
 */
namespace App\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class LoginController
{
    public function index(Request $request, Response $response)
    {
        var_dump('login');die;
        if (isset($_SESSION['user_id'])) {
            // 跳转到首页
            return $response->withRedirect('home');
        } else {
            return $this->ci->view->render($response, 'login.html.twig');
        }
    }
}