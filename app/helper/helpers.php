<?php

function msg($errcode, $data, $errmsg )
{
    return compact('errcode', 'data', 'errmsg');
}

