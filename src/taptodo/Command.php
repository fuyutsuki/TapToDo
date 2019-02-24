<?php
namespace taptodo;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;

class Command {

    public const AS_CONSOLE_TYPE = 0;
    public const AS_PLAYER_TYPE = 1;
    public const AS_OP_TYPE = 2;

    /** @var  mixed */
    private $originalCommand;
    /** @var mixed */
    private $compiledCommand;
    private $executionMode;
    /** @var TapToDo  */
    private $plugin;

    public function __construct($command, TapToDo $plugin){
        $this->originalCommand = $command;
        $this->plugin = $plugin;
        $this->compile();
    }
    public function compile(){
        if ($this->executionMode == null) {
            $this->executionMode = Command::AS_PLAYER_TYPE;
            $this->compiledCommand = $this->originalCommand;
            $this->compiledCommand = str_replace("%safe", "", $this->compiledCommand);
            if (strpos($this->compiledCommand, "%pow") !== false && ($this->compiledCommand = str_replace("%pow", "", $this->compiledCommand))) {
                $this->executionMode = Command::AS_CONSOLE_TYPE;
            } elseif (strpos($this->compiledCommand, "%op") !== false && ($this->compiledCommand = str_replace("%op", "", $this->compiledCommand))) {
                $this->executionMode = Command::AS_OP_TYPE;
            }
        }
    }
    public function execute(Player $player){
        $command = $this->compiledCommand;
        $type = $this->executionMode;
        $symbol = [
            "%p",
            "%x",
            "%y",
            "%z",
            "%l",
            "%ip",
            "%n",
        ];
        $replace = [
            $player->getName(),
            $player->getX(),
            $player->getY(),
            $player->getZ(),
            $player->getLevel()->getName(),
            $player->getAddress(),
            $player->getDisplayName(),
        ];
        $command = str_replace($symbol, $replace, $command);

        if($type === Command::AS_OP_TYPE && $player->isOp()) $type = Command::AS_PLAYER_TYPE;

        switch ($type) {
            case Command::AS_CONSOLE_TYPE:
                $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
                break;
            case Command::AS_OP_TYPE:
                $player->setOp(true);
                $this->plugin->getServer()->dispatchCommand($player, $command);
                $player->setOp(false);
                break;
            case Command::AS_PLAYER_TYPE:
                $this->plugin->getServer()->dispatchCommand($player, $command);
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getOriginalCommand() {
        return $this->originalCommand;
    }

    /**
     * @return null
     */
    public function getCompiledCommand() {
        return $this->compiledCommand;
    }

}
