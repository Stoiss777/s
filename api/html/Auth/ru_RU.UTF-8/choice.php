<?php

$settings = $this->get('settings');
$accounts = $this->get('accounts');

?><!DOCTYPE html>
<html>
    <head>
        <title>Авторизация</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            .parent {
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                overflow: auto;
            }

            .block {
                width: 160px;
                height: 160px;
                position: absolute;
                top: 50%;
                left: 55%;
                margin: -125px 0 0 -125px;

                img {
                    max-width: 100%;
                    height: auto;
                    display: block;
                    margin: 0 auto;
                    border: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="parent">
            <div class="block">
                <?php foreach ( $accounts as $account ): ?>
                    <?php foreach ( $account['services'] as $service ): ?>
                    

                        <?php if ( $service->key == 'vk' ): ?>
                            <?=$account['user']->name?>: <a href="<?=$settings['vk.uri']?>"><img src="/i/vk.png" /></a>
                        <?php endif; ?>

                            
                        <?php if ( $service->key == 'ok' ): ?>
                            <?=$account['user']->name?>: <a href="<?=$settings['ok.uri']?>"><img src="/i/ok.png" /></a>
                        <?php endif; ?>
                            

                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </body>
</html>
