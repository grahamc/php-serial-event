<?php

require_once 'lib/phpSerial.php';
require_once 'lib/PHPSerial_Event.php';

$p = new phpSerial();
$dev = '/dev/tty.usbserial-A400829n';
$dev = '/dev/cu.usbserial-A400829n';
$p->deviceSet($dev);
$p->confBaudRate(9600);

$p = new PHPSerial_Event($p);

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
    function($end, $message) {
        echo "Received start.\n";
    }
);

$p->registerPrefix('CARD:',
    function($end, $message) {
        $rand = rand(0, 10);
        echo "Recieved card: $end: $rand\n";
        return $rand;
    }
);

$p->open();
$p->read();

