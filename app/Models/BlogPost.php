<?php

namespace App\Models;

use App\Core\Application;
use App\Core\Model;

class BlogPost extends Model
{
    public string $alias;
    public string $name;
    public string $meta_title;
    public string $meta_desc;
    public string $page_title;
    public string $page_desc;
    public string $text;
    public string $text_format;
    public string $img;
    public int $view_count;
    public ?string $public_upd_at = null;
    public string $add_at;
    public string $upd_at;

    public static function getForPostingToTg(): ?self
    {
        $db = Application::$app->db;
        $sql = 'SELECT * FROM blog_posts WHERE is_tg_published = 0 ORDER BY id ASC LIMIT 1';
        return $db->fetch($sql, [], static::class, false);
    }

    public static function getPrevPost(int $post_id, $cache = false): ?self
    {
        $db = Application::$app->db;
        $table = static::getTableName();

        $sql = "SELECT * FROM `{$table}` WHERE `id` < :post_id ORDER BY `id` DESC LIMIT 1";
        return $db->fetch($sql, ['post_id' => $post_id], static::class, $cache);
    }

    public static function getNextPost(int $post_id, $cache = false): ?self
    {
        $db = Application::$app->db;
        $table = static::getTableName();

        $sql = "SELECT * FROM `{$table}` WHERE `id` > :post_id ORDER BY `id` ASC LIMIT 1";
        return $db->fetch($sql, ['post_id' => $post_id], static::class, $cache);
    }

    protected static function getTableName(): string
    {
        return 'blog_posts';
    }
}