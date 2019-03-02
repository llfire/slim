<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/28
 * Time: 15:07
 */
namespace App\MIddleware;

class Auth
{
    public function __invoke($request, $response, $next)
    {
        if (!isset($_SESSION['user_id'])) {
            return $response->withStatus(302)->withHeader('Location', '/admin/login');
        }
        $response = $next($request, $response);

        return $response;
    }
}
