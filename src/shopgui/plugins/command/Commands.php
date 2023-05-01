<?php 

declare(strict_types=1);

namespace shopgui\plugins\command;



use pocketmine\player\Player;

use pocketmine\command\Command;

use pocketmine\command\CommandSender;

use shopgui\plugins\Loader;

use shopgui\plugins\utils\Translate;



class Commands extends Command {



    use Translate;



    public function __construct(private Loader $plugin, string $nameCmd, string $desc, array $alis) {

        parent::__construct($nameCmd);

        $this->setDescription($desc);

        $this->setAliases($alis);

    }



    /**

     * @param CommandSender $player

     * @param string $label

     * @param array $args

     * @return boolean

     */

    public function execute(CommandSender $player, string $label, array $args): bool {

        if (isset($args[0]) && $args[0] === 'help') {

            $player->sendMessage($this->getHelp());

            return true;

        } elseif (isset($args[0]) && $args[0] === 'check') {

            $player->sendMessage($this->getCheck());

            return true;

        }



        if ($player instanceof Player) {

            if (isset($args[0])) {

                if (in_array($args[0], $this->plugin->getConfigs()['LoadShops'])) {

                    if (!file_exists($this->plugin->getDataFolder()."shops/".$args[0].".yml")) {

                        $player->sendMessage($this->translate(

                            [

                                "{prefix}"

                            ], [

                                $this->plugin->getPrefix()

                            ],

                            $this->plugin->getMessage()['nothave_shops']));

                        return true;

                    }



                    $shop = $this->plugin->getShop($args[0]);

                    $shop->openShop($player);

                    $this->plugin->getSound()->addSoundType($player, 'open');

                } elseif ($args[0] === 'trolly') {

                    $trolly = $this->plugin->getTrolly();

                    $trolly->openTrolly($player);

                    $this->plugin->getSound()->addSoundType($player, 'open');

                } else {

                    $this->notArgs($player);

                }

            } else {

                $this->notArgs($player);

            }

        } else {

            $player->sendMessage($this->plugin->getMessage()['use_in_game']);

            return true;

        }

        return false;

    }



    /**

     * @param Player $player

     * @return void

     */

    private function notArgs(Player $player): void {

        if ($this->plugin->getConfigs()['FormUI'] !== false) {

            $this->plugin->getForm()->sendForm($player);

            $this->plugin->getSound()->addSoundType($player, 'pop2');

        } else { 

            $player->sendMessage($this->getHelp());

        }

    }



    /**

     * @return string

     */

    private function getHelp(): string {

        $message = "";

        $message .= "§f==§d== §l§6The type to be purchased §r§d==§f==\n";

        foreach ($this->plugin->getDataShops() as $args) {

        	if (in_array($args, $this->plugin->getConfigs()['LoadShops'])) {
                $message .= "§7- /".$this->getName()." ".$args."   = Open shop ".ucfirst($args)."\n";

            }

        }

        return $message;

    }



    /**

     * @return string

     */

    private function getCheck(): string {

        $message = "";

        $message .= "§f==§d== §l§6Validation Check Shops §r§d==§f==\n";

        foreach ($this->plugin->getDataShops() as $nameData) {

            $args = $nameData." = §cNot register list config in \"LoadShops\"";

            if (in_array($nameData, $this->plugin->getConfigs()['LoadShops'])) {

                $args = $nameData." = §aEnable used this shop";

            }

            $message .= "§7- {$args}\n";

        }

        return $message;

    }

}
