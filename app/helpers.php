<?php

function canManage()
{
    return auth()->check() &&
           in_array(auth()->user()->role, ['admin','manager']);
}

function isAdmin()
{
    return auth()->check() && auth()->user()->role === 'admin';
}
