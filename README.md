# SimpleLogger
> A non-singleton, non-static logger for in-project log.


+ First, you need to create a php file to store some env constants and require it in Logger.php;

+ Then, modify member variable $logDir to your log path and make sure it's writable;

+ Modify the member function Logger::ifWriteByEnv() and use the env constants to control what level to write in different envs. 

<pre>
require 'Logger.php';

$identifier = 'test'; // a dir to distinguish in your $logerDir, e.g. /path/to/your-log-dir/test.
$logger = new Logger($identifier, __FILE__, __CLASS__);
$content = 'this is a test';
$logger->info($content, __METHOD__, __LINE__);
</pre>
