<?php
namespace Marion\Utilities;
use League\BooBoo\Formatter\HtmlFormatter;
use League\BooBoo\Util;
class ErrorHtmlFormatter extends HtmlFormatter{


    public function handleErrors(\ErrorException $e)
    {
        $errorString = '';
        if( !defined('_MARION_CONSOLE_') ){
            $errorString = "<br /><div style='z-index:9999; color:#FFFFFF; background:#f3be22; padding:10px;'><strong>%s</strong>: %s in <strong>%s</strong> on line <strong>%d</strong><br />";
        }
        

        $severity = $this->determineSeverityTextValue($e->getSeverity());

        $error = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();

        $error = sprintf($errorString, $severity, $error, $file, $line);
        if( !defined('_MARION_CONSOLE_') ){
            $error.="</div>";
        }
        return $error;
    }

    protected function formatExceptions($e)
    {
        $inspector = new Util\Inspector($e);

        $errorString = "<br /><div style='z-index:9999; color:#FFFFFF; background:#cc8f8f; padding:10px;'><strong>Fatal error:</strong> Uncaught exception '%s'";

        if ($e->getCode()) {
            $errorString .= " (" . $e->getCode() . ") ";
        }

        $errorString .= " with message '%s' in %s on line %d<br />%s<br />";

        $type = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $traceString = '#%d: %s %s<br />';
        $trace = '';

        foreach ($inspector->getFrames() as $k => $frame) {
            list($function, $fileline) = $this->processFrame($frame);
            $trace .= sprintf($traceString, $k, $function, $fileline);
        }

        $error = sprintf($errorString, $type, $message, $file, $line, $trace);

        if ($e->getPrevious()) {
            $error = $this->formatExceptions($e->getPrevious()) . $error;
        }
        $error.="</div>";
        return $error;
    }
}

?>