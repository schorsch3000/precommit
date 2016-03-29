<?php
/**
 * Created by IntelliJ IDEA.
 * User: dicky
 * Date: 29.03.16
 * Time: 21:34
 */

namespace PreCommit;


use Symfony\Component\Yaml\Yaml;

class Handler
{
    const CONFIG_FILE_NAME = 'precommit.yml';

    static public function getHookNames()
    {
        return ['pre-commit', 'post-commit'];
    }


    static public function install()
    {
        self::initConfigFile();
        $config = self::getConfig();
        foreach (self::getHookNames() as $hook) {
            if (!array_key_exists($hook, $config)) {
                continue;
            }
            $configItem = $config[$hook];
            if (!$configItem['enabled']) {
                continue;
            }
            $hookFile = '.git/hooks/' . $hook;
            if (is_file($hookFile) && !$configItem['overwrite']) {
                continue;
            }
            file_put_contents($hookFile, file_get_contents(__DIR__ . '/../hookRunner.php'));
            chmod($hookFile, 0700);

        }

    }

    static public function run($hookName)
    {
        $config = self::getConfig();
        if (!array_key_exists($hookName, $config)) {
            echo "hook: $hookName is not defined\n";
            return 1;
        }
        $hookConfig=$config[$hookName];
        if(!$hookConfig['enabled']){
            echo "hook: $hookName is disabled\n";
            return 0;
        }
        echo "Running commands:\n";
        foreach((array) $hookConfig['commands'] as $name => $command){
            echo "Running $name...\n";
            $returnCode=null;
            passthru($command,$returnCode);
            if($returnCode){
                echo "$name: ERROR command `$command` exited with code $returnCode\n";
                return $returnCode;
            }
            echo "$name: Finish\n\n";

        }
        
    }

    static protected function getConfig()
    {
        return Yaml::parse(file_get_contents(self::CONFIG_FILE_NAME));
    }

    static public function initConfigFile()
    {
        if (is_file(self::CONFIG_FILE_NAME)) {
            return false;
        }
        $defaults = ['enabled' => false, 'overwrite' => false, 'commands' => ['test' => 'phpunit']];
        $config = Yaml::dump([
            'pre-commit' => $defaults,
            'post-commit' => $defaults,

        ], 10);
        file_put_contents(self::CONFIG_FILE_NAME, $config);
    }

}