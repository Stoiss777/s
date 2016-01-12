<?php

$error = $this->get('error');

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
                <?php
                switch ($error)
                {
                    case 'access':
                    case 'cancel':
                    {
                        echo "К сожалению сервер авторизации ответил отказом. Попробуйте еще раз или воспользуйтесь другим сервисом";
                        break;
                    }
                    case 'client':
                    {
                        echo "Включить cookie";
                        break;
                    }
                    default:
                    {
                        echo "Что-то пошло не так ;-(";
                        break;
                    }
                }
                ?>
            </div>
        </div>
    </body>
</html>
