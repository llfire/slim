<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/25
 * Time: 16:32
 */

namespace App\Controller;

use App\Model\BookChapter;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface as ContainerInterface;
use App\Model\GroupPush;
use Illuminate\Database\Capsule\Manager as DB;

class GroupPushController extends BaseController
{

    protected $ci;
    public $show_chapter = 4;
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    public function index(Request $request, Response $response, $params)
    {
        $gp = explode('_', $params['gp']);
        try {
            if (!isset($gp[1])) {
                throw new \Exception('输入参数有误');
            }
            $redis = new \Redis();
            $redis->connect('r-2ze4c8fd728376d4.redis.rds.aliyuncs.com', 6379);
            $select_key = [];
            $content = '';
            //获取公众号信息
            $group_info = '';
            if ($redis->exists('group_push_info:' . $gp[1])) {
                $group_info = $redis->hGetAll('group_push_info:' . $gp[1]);
                $this->ci->logger->addInfo('获取redis群推信息成功');
            } else {
                $group_info = GroupPush::select('group_push.*', 'wx_official_accounts.wx_name', 'wx_official_accounts.connected_site')
                    ->where('externalId', $gp[1])
                    ->leftJoin('wx_official_accounts', 'wx_official_accounts.id', '=', 'group_push.app_id')
                    ->first()
                    ->toArray();

                if ($group_info) {
                    $redis->hMset('group_push_info:' . $gp[1], [
                        'msg_title' => $group_info['msg_title'],
                        'pic_url' => $group_info['pic_url'],
                        'read_url' => $group_info['connected_site'] . '?#/read/' . $group_info['book_id'] . '/' . $this->show_chapter . '?externalId=' . $group_info['externalId'],
                        'host_url' => $group_info['connected_site'] . '?#/index',
                        'wx_name' => $group_info['wx_name'],
                        'chapter_id' => $group_info['chapter_id'],
                        'book_id' => $group_info['book_id'],
                    ]);
                    $redis->expire('group_push_info:' . $gp[1], 3600);
                    $this->ci->logger->addInfo('数据库群推信息获取成功');

                } else {
                    throw new \Exception('该群推不存在');
                }
            }
            $chapter_id = $group_info['chapter_id'];
            $book_id = $group_info['book_id'];

            for ($i = 1; $i < $this->show_chapter; $i++) {
                $chapter_key = "chapter:{$book_id}:{$i}";
                $chapter_content = $redis->get($chapter_key);

                if (!$chapter_content) {
                    $chapter_content = $redis->getBit("is_empty_chapter"
                        . $book_id, "{$i}");
                    if ($chapter_content) {//如果为空的话我们跳过不进行数据库的查询避免重复一直查询不存在的章节
                        $this->ci->logger->addInfo('redis中查看章节无效' .$book_id . '章节：' . $i);
                        continue;
                    } else {
                        //                        $chapter_content = (new BookChapter)->setTable($group_push->book_id)->select('content')->where('book_id', $group_push->book_id)->where('chapter_id', $i)->first();

                        $chapter_content
                            = (new BookChapter)->setTable($book_id);

                        $chapter_content = $chapter_content->select(
                            DB::raw('book_list . title as book_title'),
                            DB::raw('book_list.cover_url as cover'),
                            'over', 'type_sex', 'content',
                            DB::raw("{$chapter_content->getTable()}.chapter_id as chapter_id"),
                            DB::raw("{$chapter_content->getTable()}.title as chapter_title"),
                            DB::raw("{$chapter_content->getTable()}.words as words"),
                            DB::raw("{$chapter_content->getTable()}.gold as gold"),
                            DB::raw("{$chapter_content->getTable()}.has_audio as has_audio"),
                            'audio_m', 'audio_f'
                        )->leftJoin('book_list',
                            "{$chapter_content->getTable()}.book_id", '=',
                            "book_list.id")
                            ->where('book_id', $book_id)
                            ->where('chapter_id', $i)
                            ->where("{$chapter_content->getTable()}.status", 2)
                            ->first();
                        if ($chapter_content) {
                            $content .= $chapter_content->content;

                            $redis_content['book_title']    = $chapter_content->book_title;
                            $redis_content['chapter_title'] = $chapter_content->chapter_title;
                            $redis_content['chapter_id']    = $chapter_content->chapter_id;
                            $redis_content['content']       = $chapter_content->content;
                            $redis_content['is_free']       = $chapter_content->gold > 0 ? 1 : 0;
                            $redis_content['gold']          = $chapter_content->gold;
                            $redis_content['over']          = $chapter_content->over;
                            $redis_content['cover']         = $chapter_content->cover;
                            $redis_content['has_audio']     = $chapter_content->has_audio;
                            $redis_content['audio_m']       = $chapter_content->audio_m;
                            $redis_content['audio_f']       = $chapter_content->audio_f;
                            $redis_content['type_sex']      = $chapter_content->type_sex;

                            // 图书最后一个章节
                            $getLast = (new BookChapter)->setTable($book_id);

                            $getLast = $getLast->select('chapter_id', 'title', 'gold', 'words', 'order')
                                ->where('book_id', $book_id)
                                ->where('status', 2)
                                ->orderByDesc('chapter_id')
                                ->first();

                            $redis_content['last_chapter_id']  = $getLast->chapter_id;
                            $chapter_content->last_chapter_id = $getLast->chapter_id;

                            $this->ci->logger->addInfo('获取图书章节成功书' . $book_id . '章节：' . $i);

                            $redis->set($chapter_key, json_encode($redis_content));
                            $redis->expire($chapter_key, 3600);
                        } else {
                            $redis->setBit("is_empty_chapter"
                                . $book_id, "{$i}", 1);
                        }
                    }
                } else {
                    $this->ci->logger->addInfo('redis获取图书章节成功书' . $book_id . '章节：' . $i);
                    $chapter_content = json_decode($chapter_content);
                    $content .= $chapter_content->content;
                }
            }

            return $this->ci->view->render($response, 'group_push.twig',
                ['group_push' => $group_info, 'content' => $content]);
        } catch (\Throwable $e) {
            $error_message = [
                'getLine' => $e->getLine(),
                'getFile' => $e->getFile(),
                'getMessage' => $e->getMessage(),
            ];
            $this->ci->logger->addInfo(json_encode($error_message));
        }
    }
}