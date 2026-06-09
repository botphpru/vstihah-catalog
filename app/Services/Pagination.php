<?php


namespace App\Services;


/**
 * CodexWorld
 *
 * Этот класс помогает интегрировать разбиение на страницы в PHP.
 *
 * @class       Pagination
 * @author      CodexWorld
 * @link        http://www.codexworld.com
 * @license     http://www.codexworld.com/license
 * @version     1.0
 */
class Pagination{
    protected $baseURL      = '';
    protected $totalRows    = '';
    protected $perPage      = 10;
    protected $numLinks     =  2;
    protected $currentPage  =  0;
    protected $firstLink    = '⟨⟨';
    protected $nextLink     = '⟩';
    protected $prevLink     = '⟨';
    protected $lastLink     = '⟩⟩';
    protected $fullTagOpen  = '<nav aria-label="Page navigation "><ul class="pagination justify-content-center pagination">';
    protected $fullTagClose = '</ul></nav>';
    protected $firstTagOpen = '';
    protected $firstTagClose= '';
    protected $lastTagOpen  = '';
    protected $lastTagClose = '';
    protected $curTagOpen   = '<li class="page-item active" aria-current="page"><span class="page-link">';
    protected $curTagClose  = '</span></li>';
    protected $nextTagOpen  = '';
    protected $nextTagClose = '';
    protected $prevTagOpen  = '';
    protected $prevTagClose = '';
    protected $numTagOpen   = '';
    protected $numTagClose  = '';
    protected $showCount    = false;
    protected $currentOffset= 0;
    protected $queryStringSegment = 'page';

    function __construct($params = array()){
        if (count($params) > 0){
            $this->initialize($params);
        }
    }

    function initialize($params = array()){
        if (count($params) > 0){
            foreach ($params as $key => $val){
                if (isset($this->$key)){
                    $this->$key = $val;
                }
            }
        }
    }

    /**
     * Генерируем ссылки на страницы
     */
    function createLinks(){
        // Если общее количество записей 0, не продолжать
        if ($this->totalRows == 0 || $this->perPage == 0){
            return '';
        }
        // Считаем общее количество страниц
        $numPages = ceil($this->totalRows / $this->perPage);
        // Если страница только одна, не продолжать
        if ($numPages == 1){
            if ($this->showCount){
                $info = 'Показаны : ' . $this->totalRows;
                return $info;
            }else{
                return '';
            }
        }

        // Определяем строку запроса
        $query_string_sep = (strpos($this->baseURL, '?') === FALSE) ? '?page=' : '&amp;page=';

        // Сохраняем чистый URL без добавленного разделителя (для ссылок на первую страницу)
        $cleanBaseURL = $this->baseURL;

        // Добавляем разделитель к baseURL для формирования ссылок на страницы 2+
        $this->baseURL = $this->baseURL . $query_string_sep;

        // Определяем текущую страницу
        $this->currentPage = $_GET[$this->queryStringSegment] ?? '1';

        if (!is_numeric($this->currentPage) || $this->currentPage == 0){
            $this->currentPage = 1;
        }

        // Строковая переменная вывода контента
        $output = '';

        // Отображаем сообщение о ссылках на другие страницы
        if ($this->showCount){
            $currentOffset = ($this->currentPage > 1)?($this->currentPage - 1)*$this->perPage:$this->currentPage;
            $info = 'Показаны элементы с ' . $currentOffset . ' по ' ;

            if( ($currentOffset + $this->perPage) < $this->totalRows )
                $info .= $this->currentPage * $this->perPage;
            else
                $info .= $this->totalRows;

            $info .= ' из ' . $this->totalRows . ' <br/> ';

            $output .= $info;
        }

        $this->numLinks = (int)$this->numLinks;

        // Если номер страницы больше максимального значения, отображаем последнюю страницу
        if($this->currentPage > $this->totalRows){
            $this->currentPage = $numPages;
        }

        $uriPageNum = $this->currentPage;

        // Рассчитываем первый и последний элементы
        $start = (($this->currentPage - $this->numLinks) > 0) ? $this->currentPage - ($this->numLinks - 1) : 1;
        $end   = (($this->currentPage + $this->numLinks) < $numPages) ? $this->currentPage + $this->numLinks : $numPages;

        // Выводим ссылку на первую страницу
        if($this->currentPage > $this->numLinks){
            // Используем чистый URL без параметра page
            $firstPageURL = $cleanBaseURL;
            $output .= $this->firstTagOpen.'<li class="page-item"><a href="'.$firstPageURL.'" class="page-link">'.$this->firstLink.'</a></li>'.$this->firstTagClose;
        }
        // Выводим ссылку на предыдущую страницу
        if($this->currentPage != 1){
            $i = ($uriPageNum - 1);
            if($i == 0) $i = '';
            // Если предыдущая страница – первая (номер 1), ссылка ведёт на чистый URL
            $link = ($i == 1) ? $cleanBaseURL : $this->baseURL . $i;
            $output .= $this->prevTagOpen.'<li class="page-item"><a href="'.$link.'" class="page-link">'.$this->prevLink.'</a></li>'.$this->prevTagClose;
        }
        // Выводим цифровые ссылки
        for($loop = $start -1; $loop <= $end; $loop++){
            $i = $loop;
            if($i >= 1){
                if($this->currentPage == $loop){
                    $output .= $this->curTagOpen.$loop.$this->curTagClose;
                }else{
                    // Для страницы 1 используем чистый URL
                    $link = ($i == 1) ? $cleanBaseURL : $this->baseURL . $i;
                    $output .= $this->numTagOpen.'<li class="page-item"><a href="'.$link.'" class="page-link">'.$loop.'</a></li>'.$this->numTagClose;
                }
            }
        }
        // Выводим ссылку на следующую страницу
        if($this->currentPage < $numPages){
            $i = ($this->currentPage + 1);
            $output .= $this->nextTagOpen.'<li class="page-item"><a href="'.$this->baseURL.$i.'" class="page-link">'.$this->nextLink.'</a></li>'.$this->nextTagClose;
        }
        // Выводим ссылку на последнюю страницу
        if(($this->currentPage + $this->numLinks) < $numPages){
            $i = $numPages;
            $output .= $this->lastTagOpen.'<li class="page-item"><a href="'.$this->baseURL.$i.'" class="page-link">'.$this->lastLink.'</a></li>'.$this->lastTagClose;
        }
        // Удаляем двойные косые
        $output = preg_replace("#([^:])//+#", "\1/", $output);
        // Добавляем открывающий и закрывающий тэги блока
        $output = $this->fullTagOpen.$output.$this->fullTagClose;

        return $output;
    }
}