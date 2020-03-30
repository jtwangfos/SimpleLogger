# SimpleLogger

A non-singleton, non-static logger for in-project log.

First, you need to create a file for some env constants and require it in Logger.php;
Then, modify member $logDir to your log path and make sure it's writable;
Change the member function Logger::ifWriteByEnv() and use the env constants to control what levels to write in different envs. 
<pre>
require 'Logger.php';
$identifier = 'test'; // some dir to distinguish dirs in your $logerDir
$logger = new Logger($identifier, __FILE__, __CLASS__);
$content = 'this is a test';
$logger->info($content, __METHOD__, __LINE__);
</pre>
