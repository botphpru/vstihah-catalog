<section>
    <div class="container-lg my-4">
        <h1>Логи</h1>
        <div class="row">
            <? foreach ($filesLogs as $key => $logs):?>
                <? if($logs):?>
                    <div class="col-lg-4 mb-2">
                        <div class="card h-100">
                            <div class="card-body p-3">
                                <h5 class="card-title"><? echo $key;?></h5>
                                <ul class="list-group list-group-flush">
                                    <? foreach ($logs as $file):
                                        $cssClass = '';
                                        if($file == date("Y-m-d").'.txt') $cssClass = 'text-danger';
                                        ?>
                                        <li class="list-group-item"><a href="/admin123/logs/<? echo $key;?>/<? echo $file;?>" class="<? echo $cssClass;?>" target="_blank"><? echo $file;?></a></li>
                                    <?endforeach;?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <? endif;?>
            <? endforeach;?>
        </div>
    </div>
</section>