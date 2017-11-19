<?php

namespace instances;

interface SmsAccessInstance
{
    public function getClass():string;

    public function getKey():string;
}