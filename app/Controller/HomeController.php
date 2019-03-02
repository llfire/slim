<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/25
 * Time: 16:32
 */
namespace App\Controller;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface as ContainerInterface;

class HomeController extends BaseController
{
    protected $ci;

    public function __construct(ContainerInterface $ci)
    {
        $this->ci    = $ci;
    }

    public function index($request, $response, $params)
    {
//        var_dump(1111);
//        return $this->view->render($response, 'bookshelf/book/list.twig', [
//            'books' => Book::all(),
//        ]);
    }
}