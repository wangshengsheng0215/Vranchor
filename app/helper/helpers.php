<?php

function msg($code, $data, $msg )
{
    return compact('errcode', 'data', 'errmsg');
}

