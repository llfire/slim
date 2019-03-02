<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BookChapter extends Model
{
    /**
     * 数据模型专属的数据库连接
     *
     * @var string
     */
    protected $connection = 'db_stack';

    /**
     * @var string
     */
    protected static $tableName = 'book_chapter';

    /**
     * 分表数量
     *
     * @var int
     */
    protected static $tableNumber = 20;
    protected $guarded = ['id', 'book_id', 'chapter_id'];
    /**
     * Set the table associated with the model.
     *
     * @param integer $bookId
     * @return $this
     */
    public function setTable($bookId)
    {
        $tableName = self::$tableName;
        $num       = self::$tableNumber;
        $checkNum  = sprintf("%u", crc32($bookId));
        $no        = str_pad((string)(bcmod($checkNum, $num)), 2, '0', STR_PAD_LEFT);

        $this->table = $tableName . "_" . $no;

        return $this;
    }
}
