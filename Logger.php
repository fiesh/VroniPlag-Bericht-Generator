<?php

class Logger {

    private static $logFile = 'debug.log';

	public static function dump($variable) {
        $logHandle = fopen(self::$logFile, 'a');
        ob_start();
        var_dump($variable);
        $buffer = ob_get_clean();
        fwrite($logHandle, $buffer);
        fwrite($logHandle, "\n\n");
        fclose($logHandle);
    }

    public static function log($message) {
        $logHandle = fopen(self::$logFile, 'a');
        fwrite($logHandle, $message);
        fwrite($logHandle, "\n\n");
        fclose($logHandle);
    }

}