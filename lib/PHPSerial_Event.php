<?php

class PHPSerial_Event
{
    protected $serial;
    protected $events;
    protected $prefixes;

    public function __construct(phpSerial $serial)
    {
        $this->serial = $serial;
    }

    public function open($maxTries = 0)
    {
        $attempt = 0;

        while (true) {
            if ($maxTries != 0 && $attempt++ > $maxTries) {
                $this->emit('connect.abort', $attempt);
                return false;
            }

            $this->emit('connect.attempt', $attempt);

            if ($this->serial->deviceOpen()) {
                $this->emit('connect.success', $attempt);
                break;
            } else {
                $this->emit('connect.fail', $attempt);
                sleep(1);
            }
        }

        return true;
    }

    public function read()
    {
        while (true) {
            $read = $this->serial->readPort();
            if ($read) {
                $msg = trim($read);

                foreach ($this->prefixes as $prefix) {
                    $matchSection = substr($msg, 0, strlen($prefix));
                    if ($matchSection == $prefix) {
                        $endSection = substr($msg, strlen($prefix));

                        $results = $this->emit('message.received.prefix.' . $prefix, $endSection, $msg);

                        foreach ($results as $result) {
                            if ($result !== false && !is_null($result)) {
                                $this->serial->sendMessage($result);
                            }
                        }
                        continue 2;
                    }
                }
            } else {
                sleep(1);
            }
        }
    }

    public function registerPrefix($prefix, $callback)
    {
        $this->connect('message.received.prefix.' . $prefix, $callback);
        $this->prefixes[$prefix]  = $prefix;
    }

    public function connect($event, $callback)
    {
        if (is_callable($callback)) {
            if (!isset($this->events[$event])) {
                $this->events[$event] = array();
            }

            $this->events[$event][] = $callback;
        }
    }

    protected function emit($event)
    {
        $args = func_get_args();
        array_shift($args);
        if (!isset($this->events[$event])) {
            return;
        }

        $results = array();
        foreach ($this->events[$event] as $callback) {
            $results[] = call_user_func_array($callback, $args);
        }

        return $results;
    }
}
