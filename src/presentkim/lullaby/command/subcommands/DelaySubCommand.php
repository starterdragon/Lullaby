<?php

namespace presentkim\lullaby\command\subcommands;

use pocketmine\command\CommandSender;
use presentkim\lullaby\Lullaby as Plugin;
use presentkim\lullaby\command\{
  PoolCommand, SubCommand
};
use presentkim\lullaby\util\Utils;

class DelaySubCommand extends SubCommand{

    public function __construct(PoolCommand $owner){
        parent::__construct($owner, 'delay');
    }

    /**
     * @param CommandSender $sender
     * @param String[]      $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, array $args) : bool{
        if (isset($args[0])) {
            $delay = Utils::toInt($args[0], null, function (int $i){
                return $i >= 0;
            });
            if ($delay === null) {
                $sender->sendMessage(Plugin::$prefix . $this->translate('failure', $args[0]));
            } else {
                $this->plugin->getConfig()->set("delay", $delay);
                $sender->sendMessage(Plugin::$prefix . $this->translate('success', $delay));
            }
            return true;
        }
        return false;
    }
}