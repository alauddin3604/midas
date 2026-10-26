<?php

declare(strict_types=1);

arch('Support does not depend on Domain or App')
    ->expect('Support')
    ->not->toUse(['Domain', 'App']);
