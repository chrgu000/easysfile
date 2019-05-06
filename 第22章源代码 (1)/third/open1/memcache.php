<?php
if (isset($_SERVER['HTTP_APPNAME'])){
    $mem = memcache_init();
    
    
    
    $all_items = $mem->getExtendedStats('items');
    var_export($all_items);
}