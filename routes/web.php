<?php

require __DIR__.'/marketing.php';
require __DIR__.'/app.php';
require __DIR__.'/dev.php';

if (config('features.admin.enabled', false)) {
    require __DIR__.'/admin.php';
}

require __DIR__.'/auth.php';
