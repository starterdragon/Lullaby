<?php

namespace presentkim\lullaby;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use presentkim\lullaby\command\PoolCommand;
use presentkim\lullaby\command\subcommands\{
  DelaySubCommand, HealSubCommand, LangSubCommand, ReloadSubCommand, SaveSubCommand
};
use presentkim\lullaby\listener\PlayerEventListener;
use presentkim\lullaby\task\SetSleepTickTask;
use presentkim\lullaby\util\Translation;

class Lullaby extends PluginBase{

    /** @var self */
    private static $instance = null;

    /** @var string */
    public static $prefix = '';

    /** @var TaskHandler */
    private $taskHandler = null;

    /** @return self */
    public static function getInstance() : self{
        return self::$instance;
    }

    /** @var PoolCommand */
    private $command;

    public function onLoad() : void{
        if (self::$instance === null) {
            self::$instance = $this;
            Translation::loadFromResource($this->getResource('lang/eng.yml'), true);
        }
    }

    public function onEnable() : void{
        $this->load();
        $this->getServer()->getPluginManager()->registerEvents(new PlayerEventListener(), $this);

        $this->taskHandler = $this->getServer()->getScheduler()->scheduleRepeatingTask(new SetSleepTickTask($this), 30);
    }

    public function onDisable() : void{
        $this->save();
        $this->taskHandler->cancel();
    }

    public function load() : void{
        $dataFolder = $this->getDataFolder();
        if (!file_exists($dataFolder)) {
            mkdir($dataFolder, 0777, true);
        }

        $this->reloadConfig();

        $langfilename = $dataFolder . 'lang.yml';
        if (!file_exists($langfilename)) {
            $resource = $this->getResource('lang/eng.yml');
            fwrite($fp = fopen("{$dataFolder}lang.yml", "wb"), $contents = stream_get_contents($resource));
            fclose($fp);
            Translation::loadFromContents($contents);
        } else {
            Translation::load($langfilename);
        }

        self::$prefix = Translation::translate('prefix');
        $this->reloadCommand();
    }

    public function save() : void{
        $dataFolder = $this->getDataFolder();
        if (!file_exists($dataFolder)) {
            mkdir($dataFolder, 0777, true);
        }

        $this->saveConfig();
    }

    public function reloadCommand() : void{
        if ($this->command == null) {
            $this->command = new PoolCommand($this, 'lullaby');
            $this->command->createSubCommand(DelaySubCommand::class);
            $this->command->createSubCommand(HealSubCommand::class);
            $this->command->createSubCommand(LangSubCommand::class);
            $this->command->createSubCommand(ReloadSubCommand::class);
            $this->command->createSubCommand(SaveSubCommand::class);
        }
        $this->command->updateTranslation();
        $this->command->updateSudCommandTranslation();
        if ($this->command->isRegistered()) {
            $this->getServer()->getCommandMap()->unregister($this->command);
        }
        $this->getServer()->getCommandMap()->register(strtolower($this->getName()), $this->command);
    }

    /**
     * @param string $name = ''
     *
     * @return PoolCommand
     */
    public function getCommand(string $name = '') : PoolCommand{
        return $this->command;
    }

    /** @param PoolCommand $command */
    public function setCommand(PoolCommand $command) : void{
        $this->command = $command;
    }
}
