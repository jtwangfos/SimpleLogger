<?php

/**
 * Created by PhpStorm.
 * User: witness
 * Date: 2020/2/12
 * Time: 1:17 PM
 */
require_once dirname(dirname(__FILE__)) . '/constants/env.php';

class Logger {
    const LOG_LEVEL_TRACE = 0;
    const LOG_LEVEL_DEBUG = 1;
    const LOG_LEVEL_INFO = 2;
    const LOG_LEVEL_WARN = 3;
    const LOG_LEVEL_ERROR = 4;
    const LOG_LEVEL_FATAL = 5;

    protected $levelText = [
        self::LOG_LEVEL_TRACE => 'TRACE',
        self::LOG_LEVEL_DEBUG => 'DEBUG',
        self::LOG_LEVEL_INFO => 'INFO',
        self::LOG_LEVEL_WARN => 'WARN',
        self::LOG_LEVEL_ERROR => 'ERROR',
        self::LOG_LEVEL_FATAL => 'FATAL',
    ];

    protected $logName = '%s.log'; // default: datetime

    protected $logFormat = "[%s][%s][%d] %s"; // default: datetime, level, pid, log
    protected $logWithFileName = " in %s";
    protected $logWithClassName = ": %s";
    protected $logWithMethodName = ": %s()";
    protected $logWithLine = " at %s";
    protected $logEOF = "\n";

    protected $logDir = LOG_DIR;
    protected $logWithIdentifierDir = '%s/';

    protected $identifier;
    protected $fileName;
    protected $className;
    protected $pid;

    protected $methodName;
    protected $line;

    protected $currentLog;
    protected $currentLevel;

    protected $logFuncNameList = [
        'trace' => 1,
        'debug' => 1,
        'info' => 1,
        'warn' => 1,
        'error' => 1,
        'fatal' => 1,
    ];

    public function __construct($identifier = null, $fileName = null, $className = null) {
        if (isset($identifier)) {
            $this->identifier = $identifier;
            $this->logDir = sprintf($this->logDir . $this->logWithIdentifierDir, $this->identifier);
        }
        if (! is_dir($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }
        if (isset($fileName)) {
            $this->fileName = $fileName;
        }
        if (isset($className)) {
            $this->className = $className;
        }
        $this->pid = getmypid();
    }

    public function __call($func, $args) {
        if (isset($this->logFuncNameList[$func])) {
            if (empty($args[0])) {
                return;
            }
            $this->currentLog = $args[0];
            if (isset($args[1])) {
                $this->methodName = $args[1];
            }
            if (isset($args[2])) {
                $this->line = $args[2];
            }
            call_user_func_array([$this, $func], $args);
        }
    }

    protected function trace($log, $method = null, $line = null) {
        $this->currentLevel = self::LOG_LEVEL_TRACE;
        $this->write();
    }

    protected function debug($log, $method = null, $line = null) {
        $this->currentLevel = self::LOG_LEVEL_DEBUG;
        $this->write();
    }

    protected function info($log, $method = null, $line = null) {
        $this->currentLevel = self::LOG_LEVEL_INFO;
        $this->write();
    }

    protected function warn($log, $method = null, $line = null) {
        $this->currentLevel = self::LOG_LEVEL_WARN;
        $this->write();
    }

    protected function error($log, $method = null, $line = null) {
        $this->currentLevel = self::LOG_LEVEL_ERROR;
        $this->write();
    }

    protected function fatal($log, $method = null, $line = null) {
        $this->currentLevel = self::LOG_LEVEL_FATAL;
        $this->write();
    }

    protected function write() {
        if (! $this->ifWriteByEnv()) {
            return;
        }

        file_put_contents($this->getLogFilePath(), $this->getLogContent(), FILE_APPEND);
        unset($this->currentLog, $this->methodName, $this->line);
    }

    protected function getLogFilePath() {
        return sprintf($this->logDir . $this->logName, date('YmdH'));
    }

    protected function getLogContent() {
        if (is_array($this->currentLog)) {
            $this->currentLog = json_encode($this->currentLog);
        } else if (is_object($this->currentLog)) {
            $this->currentLog = json_encode($this->currentLog, JSON_FORCE_OBJECT);
        }

        $logFormat = $this->logFormat;
        $args = [date('Y-m-d H:i:s'), $this->levelText[$this->currentLevel], $this->pid, $this->currentLog];
        if (isset($this->fileName)) {
            $logFormat .= $this->logWithFileName;
            $args[] = $this->fileName;
        }
        if (isset($this->className)) {
            if (! isset($this->methodName)) {
                $logFormat .= $this->logWithClassName;
                $args[] = $this->className;
            }
        }
        if (isset($this->methodName)) {
            $logFormat .= $this->logWithMethodName;
            $args[] = $this->methodName;
        }
        if (isset($this->line)) {
            $logFormat .= $this->logWithLine;
            $args[] = $this->line;
        }
        $logFormat .= $this->logEOF;

        return sprintf($logFormat, ...$args);
    }

    protected function ifWriteByEnv() {
        if (ENV > ENV_TEST) {
            if ($this->currentLevel >= self::LOG_LEVEL_INFO) {
                return true;
            }

            return false;
        }

        return true;
    }

}