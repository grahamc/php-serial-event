<?php
namespace GrahamC\Serial;

interface Device
{


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
    function deviceSet ($device);

    /**
     * Opens the device for reading and/or writing.
     *
     * @param string $mode Opening mode : same parameter as fopen()
     * @return bool
     */
    function deviceOpen ($mode = "r+b");

    /**
     * Closes the device
     *
     * @return bool
     */
    function deviceClose ();

    //
    // OPEN/CLOSE DEVICE SECTION -- {STOP}
    //

    //
    // CONFIGURE SECTION -- {START}
    //

    /**
     * Configure the Baud Rate
     * Possible rates : 110, 150, 300, 600, 1200, 2400, 4800, 9600, 38400,
     * 57600 and 115200.
     *
     * @param int $rate the rate to set the port in
     * @return bool
     */
    function confBaudRate ($rate);

    /**
     * Configure parity.
     * Modes : odd, even, none
     *
     * @param string $parity one of the modes
     * @return bool
     */
    function confParity ($parity);

    /**
     * Sets the length of a character.
     *
     * @param int $int length of a character (5 <= length <= 8)
     * @return bool
     */
    function confCharacterLength ($int);

    /**
     * Sets the length of stop bits.
     *
     * @param float $length the length of a stop bit. It must be either 1,
     * 1.5 or 2. 1.5 is not supported under linux and on some computers.
     * @return bool
     */
    function confStopBits ($length);

    /**
     * Configures the flow control
     *
     * @param string $mode Set the flow control mode. Availible modes :
     *  -> "none" : no flow control
     *  -> "rts/cts" : use RTS/CTS handshaking
     *  -> "xon/xoff" : use XON/XOFF protocol
     * @return bool
     */
    function confFlowControl ($mode);

    //
    // CONFIGURE SECTION -- {STOP}
    //

    //
    // I/O SECTION -- {START}
    //

    /**
     * Sends a string to the device
     *
     * @param string $str string to be sent to the device
     * @param float $waitForReply time to wait for the reply (in seconds)
     */
    function sendMessage ($str, $waitForReply = 0.1);

    /**
     * Reads the port until no new datas are availible, then return the content.
     *
     * @pararm int $count number of characters to be read (will stop before
     *  if less characters are in the buffer)
     * @return string
     */
    function readPort ($count = 0);

    /**
     * Flushes the output buffer
     * Renamed from flush for osx compat. issues
     *
     * @return bool
     */
    function serialflush ();
}
?>
