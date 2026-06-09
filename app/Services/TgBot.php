<?php


namespace App\Services;


class TgBot
{
    private static $instance;

    private $token;
    private $chat_admin;

    private $disable_notification = true; //уведомления отключены
    private $disable_web_page_preview = true; //превью сайта отключено.
    private $parse_mode = 'html'; //разметка



    public static function init()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->token = BOT_TOKEN;
        $this->chat_admin = NOTICE_CHANNEL;
    }

    public function sendMessage($user_id, $text, $keyboard = NULL){
        $data_send = [
            'text' => $text,
            'chat_id' => $user_id,
            'disable_notification'=>$this->getDisable_notification(),
            'disable_web_page_preview'=>$this->disable_web_page_preview,
            'parse_mode'=>$this->parse_mode,
        ];
        if($keyboard) {
            // Превращаем массив в JSON, если он еще не JSON
            $data_send['reply_markup'] = is_array($keyboard) ? json_encode($keyboard) : $keyboard;
        }
        return $this->botApiQuery("sendMessage", $data_send);
    }

    public static function sendNotice($text)
    {
        $bot = self::init();
        return $bot->sendToAdminChat($text);
    }

    public function sendToAdminChat(string $text) {
        return $this->sendMessage($this->chat_admin, $text);
    }

    public function getDisable_notification(){ //включаем звук уведомлений и вибрацию после 9ти утра до 9ти вечера по времени сервера
        $hour = date("H");
        if($hour>=9 and $hour<21) {
            $this->disable_notification = false;
        }
        return $this->disable_notification;
    }

    private function botApiQuery($method, $data = array()){
        $ch = curl_init('https://api.t-ru.ru/bot' . $this->token . '/' . $method);
        curl_setopt_array($ch, [
            CURLOPT_POST => count($data),
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 10
        ]);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $res;
    }
    public function setWebPagePreview() {
        $this->disable_web_page_preview = 0;
    }
    public function sendPhoto($user_id, $text, $photo, $keyboard = NULL){
        $data_send = [
            'caption' => $text,
            'chat_id' => $user_id,
            'photo' => $photo,
            'disable_notification'=>$this->getDisable_notification(),
            'disable_web_page_preview'=>$this->disable_web_page_preview,
            'parse_mode'=>$this->parse_mode,
        ];
        if($keyboard) {
            // Превращаем массив в JSON, если он еще не JSON
            $data_send['reply_markup'] = is_array($keyboard) ? json_encode($keyboard) : $keyboard;
        }
        return $this->botApiQuery("sendPhoto", $data_send);
    }

    /**
     * Подготавливает HTML-текст для публикации в Telegram Bot API.
     *
     * @param string $html Исходный текст в формате HTML.
     * @return string Валидный и читаемый текст для ParseMode::HTML.
     */
    public function prepareTextForTelegram(string $html): string {
        // 1. Унифицируем переносы строк и чистим форматирование кода
        $html = str_replace(["\r\n", "\r"], "\n", $html);
        // Убираем переносы между тегами, чтобы они не создавали лишних пустот
        $html = preg_replace('/>\s+</', '><', $html);

        // НЮАНС: Telegram не понимает многие HTML-сущности (кроме &lt;, &gt;, &amp;, &quot;).
        // Поэтому популярные неразрывные пробелы и кавычки лучше сразу превратить в текст.
        $html = str_replace(
            ['&nbsp;', '&mdash;', '&ndash;', '&laquo;', '&raquo;'],
            [' ', '—', '–', '«', '»'],
            $html
        );

        // 2. Заголовки (h1-h6): делаем жирными и ставим перенос строки
        $html = preg_replace('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', "<b>$1</b>\n", $html);

        // 3. Элементы списка (li): заменяем на дефис
        $html = preg_replace('/<li[^>]*>(.*?)<\/li>/is', "- $1\n", $html);

        // 4. Абзацы (p): ставим двойной перенос после окончания абзаца
        $html = preg_replace('/<\/p>/is', "\n\n", $html);

        // 5. Ссылки (a): вырезаем все атрибуты, кроме href
        // Регулярка захватывает URL (с кавычками или без) и анкор текста
        $html = preg_replace('/<a\s+[^>]*href=(["\']?)([^"\'\s>]+)\1[^>]*>(.*?)<\/a>/is', '<a href="$2">$3</a>', $html);

        // 6. Блочные элементы, которые удаляем: меняем на перенос строки, чтобы текст "не слипся"
        $html = preg_replace('/<\/?(?:br|div|ul|ol|table|tr)[^>]*\/?>/is', "\n", $html);
        $html = preg_replace('/<\/(?:td|th)>/is', " ", $html); // ячейки таблиц разделяем пробелом

        // 7. Удаляем все теги, КРОМЕ разрешенных в Telegram
        $allowedTags = '<b><strong><i><em><u><s><strike><del><a><code><pre><tg-spoiler>';
        $html = strip_tags($html, $allowedTags);

        // 8. Зачистка: превращаем 3 и более переносов строк подряд в аккуратный двойной перенос
        $html = preg_replace("/\n{3,}/", "\n\n", $html);

        // 9. Финальный тримминг по краям
        return trim($html);
    }
}