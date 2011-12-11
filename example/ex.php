<?php

require_once __DIR__ . '/../GrahamC/Serial/Device.php';
require_once __DIR__ . '/../GrahamC/Serial/Event.php';
require_once __DIR__ . '/../GrahamC/Serial/Device/Physical.php';
require_once __DIR__ . '/../GrahamC/Serial/Device/Mock.php';

use GrahamC\Serial\Event as Event;
use GrahamC\Serial\Device\Mock as Mock;


$messages = array('START');


$device = new Mock(
        function() use(&$messages)
        {
            return array_pop($messages);
        },
        null);

$device->deviceSet('example');

$p = new Event($device);

$p->connect('connect.attempt',
    function($attempt) {
        echo "Attempting to open...\t";
    }
);

$p->connect('connect.success',
    function($attempt) {
        echo "Success on $attempt\n";
    }
);

$p->connect('connect.fail',
    function($attempt) {
        echo "Failure on $attempt\n";
    }
);

$p->connect('connect.abort',
    function($attempt) {
        echo "Abording connection on attempt #$attempt\n";
    }
);

$p->registerPrefix('START',
    function($end, $message) use (&$messages) {
        echo "Received start.\n";
        $messages[] = 'CARD:123';
    }
);

$p->registerPrefix('CARD:',
    function($end, $message) use (&$messages) {
        $messages[] = 'CARD:' . uniqid();

        $rand = rand(0, 10);
        echo "Received card: $end: $rand\n";
        return $rand;
    }
);

$p->open();
$p->read();

