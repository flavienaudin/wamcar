<?php


namespace AppBundle\Services\App;


interface CaptchaVerificator
{
    public function getClientSidePostParameters();
    public function verify(array $data): array;
}