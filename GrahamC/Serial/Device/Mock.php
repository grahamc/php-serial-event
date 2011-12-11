<?php

namespace GrahamC\Serial\Device;
use GrahamC\Serial\Device as Device;

class Mock implements Device
{

    protected $device_set = false;
    protected $open = false;

    protected $callback_read;
    protected $callback_write;

    public function __construct($read, $write)
    {
        if (is_callable($read)) {
            $this->callback_read = $read;
        } else {
            $this->callback_read = function() { return ''; };
        }

        if (is_callable($write)) {
            $this->callback_write = $write;
        } else {
            $this->callback_write = function($str) { return ''; };
        }
    }

    /**
     * Device set function : used to set the device name/address.
     * -> linux : use the device address, like /dev/ttyS0
     * -> osx : use the device address, like /dev/tty.serial
     * -> windows : use the COMxx device name, like COM1 (can also be used
     *     with linux)
     *
     * @param string $device the name of the device to be used
     * @return bool
     */
    function deviceSet ($device)
    {
        if ($this->open) {
            return false;
        }

        $this->device_set = true;
    }

    /**
     * Opens the device for reading and/or writing.
     *
     * @param string $mode Opening mode : same parameter as fopen()
     * @return bool
     */
    function deviceOpen ($mode = "r+b")
    {
        if (!$this->device_set) {
            return false;
        }

        $this->open = true;

        return true;
    }

    /**
     * Closes the device
     *
     * @return bool
     */
    function deviceClose ()
    {
        $this->open = false;
    }

    /**
     * Configure the Baud Rate
     * Possible rates : 110, 150, 300, 600, 1200, 2400, 4800, 9600, 38400,
     * 57600 and 115200.
     *
     * @param int $rate the rate to set the port in
     * @return bool
     */
    function confBaudRate ($rate)
    {
        if (!$this->device_set || $this->open) {
            return false;
        }

        $validBauds = array (
            110    => 11,
            150    => 15,
            300    => 30,
            600    => 60,
            1200   => 12,
            2400   => 24,
            4800   => 48,
            9600   => 96,
            19200  => 19,
            38400  => 38400,
            57600  => 57600,
            115200 => 115200
        );

        return isset($validBauds[$rate]);
    }

    /**
     * Configure parity.
     * Modes : odd, even, none
     *
     * @param string $parity one of the modes
     * @return bool
     */
    function confParity ($parity)
    {
        if (!$this->device_set || $this->open) {
            return false;
        }

        $args = array(
            "none" => "-parenb",
            "odd"  => "parenb parodd",
            "even" => "parenb -parodd",
        );

        return isset($args[$parity]);
    }

    /**
     * Sets the length of a character.
     *
     * @param int $int length of a character (5 <= length <= 8)
     * @return bool
     */
    function confCharacterLength ($int)
    {
        if (!$this->device_set || $this->open) {
            return false;
        }

        return true;
    }

    /**
     * Sets the length of stop bits.
     *
     * @param float $length the length of a stop bit. It must be either 1,
     * 1.5 or 2. 1.5 is not supported under linux and on some computers.
     * @return bool
     */
    function confStopBits ($length)
    {
        if (!$this->device_set || $this->open) {
            return false;
        }

        $options = array(1, 1.5, 2);

        return in_array($length, $options);
    }

    /**
     * Configures the flow control
     *
     * @param string $mode Set the flow control mode. Availible modes :
     *  -> "none" : no flow control
     *  -> "rts/cts" : use RTS/CTS handshaking
     *  -> "xon/xoff" : use XON/XOFF protocol
     * @return bool
     */
    function confFlowControl ($mode)
    {
        if (!$this->device_set || $this->open) {
            return false;
        }

        $modes = array(
            "none",
            "rts/cts",
            "xon/xoff"
        );

        return in_array($mode, $modes);
    }

    /**
     * Sends a string to the device
     *
     * @param string $str string to be sent to the device
     * @param float $waitForReply time to wait for the reply (in seconds)
     */
    function sendMessage ($str, $waitForReply = 0.1)
    {
        if (!$this->open) {
            return false;
        }

        call_user_func($this->callback_write, $str);

        usleep((int) ($waitForReply * 1000000));
    }

    /**
     * Reads the port until no new datas are availible, then return the content.
     *
     * @pararm int $count number of characters to be read (will stop before
     *  if less characters are in the buffer)
     * @return string
     */
    function readPort ($count = 0)
    {
        return call_user_func($this->callback_read);
    }

    /**
     * Flushes the output buffer
     * Renamed from flush for osx compat. issues
     *
     * @return bool
     */
    function serialflush ()
    {
        if (!$this->_ckOpened()) return false;

        if (fwrite($this->_dHandle, $this->_buffer) !== false)
        {
            $this->_buffer = "";
            return true;
        }
        else
        {
            $this->_buffer = "";
            trigger_error("Error while sending message", E_USER_WARNING);
            return false;
        }
    }
}

