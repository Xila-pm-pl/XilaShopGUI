<?php declare(strict_types = 1);
namespace shopgui\plugins\manager;

use muqsit\invmenu\transaction\InvMenuTransaction;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\player\Player;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use shopgui\plugins\inventory\ChestShop;
use shopgui\plugins\Loader;

class ManagerShop {

    /** @var array */
    private array $config;

    public function __construct(private Loader $plugin, string $shop_type) {
        $this->config = $plugin->getDataShop($shop_type)->getAll();
    }

    /**
     * @param Player $player
     * @param integer $page
     * @return void
     */
    public function openShop(Player $player, int $page = 0) {
        $chest = $this->getChest();
        $chest->setPage($page);
        
        $chest->setListener(function(InvMenuTransaction $transaction) use ($chest) : InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $itemClicked = $transaction->getItemClicked();
            $action = $transaction->getAction();

            $inventory = $chest->getInventory();
            $soundAPI = $this->plugin->getSound();

            if ($chest->itemClicked("next", $itemClicked)) {
                $chest->onClose($player);

                return $transaction->discard()->then(function(Player $player) use ($chest, $soundAPI) : void {
                    $soundAPI->addSoundType($player, 'pop1');
                    $this->openShop($player, $chest->getPage()+1);
                });
            } elseif ($chest->itemClicked("back", $itemClicked)) {
                $chest->onClose($player);

                return $transaction->discard()->then(function(Player $player) use ($chest, $soundAPI) : void {
                    $soundAPI->addSoundType($player, 'pop1');
                    $this->openShop($player, $chest->getPage()-1);
                });
            } elseif ($chest->itemClicked("outline", $itemClicked)) {
                $soundAPI->addSoundType($player, 'pop2');
            } else {
                $this->handel($player, $action, $chest);
            }

            return $transaction->discard();
        });
        
        $chest->setItems($this->config['Shops']);
        $chest->setOutLine($this->config['Shops']);

        $chest->setInventoryCloseListener(function(Player $player, Inventory $inventory) : void {
            if ($this->plugin->getConfigs()['FormUI'] !== false) {
                $this->plugin->getForm()->sendForm($player);
            } else {
            	$this->plugin->getSound()->addSoundType($player, 'closed');
            }
        });

        $chest->sendTo($player);
    }

    /**
     * @param Player $player
     * @param SlotChangeAction $action
     * @param ChestShop $chest
     * @return void
     */
    private function handel(Player $player, SlotChangeAction $action, ChestShop $chest) {
        foreach ($this->config['Shops'] as $page => $list) {
            if ($page === $chest->getPage()) {
                foreach ($list['list'] as $slots => $data_shop) {
                    if ((int)$slots === $action->getSlot()) {
                        $dataTrolly = Loader::getDataTrolly();
                        $array = explode('--', $data_shop);
                        $soundAPI = $this->plugin->getSound();
                        $format_data = ($id = $chest->registerItem($array[0], 'id')) . ':' . ($meta = $chest->registerItem($array[0], 'meta')) . '--' . ($price = $array[1]).'--'.($amount = (isset($array[2])) ? (int)$array[2] : 1);

                        if (count($dataTrolly->getTrolly($player->getName())) < 44) {
                            $dataTrolly->addTrolly($player->getName(), $format_data);
                            $player->sendMessage($chest->translate(
                                [
                                    "{prefix}",
                                    "{item_name}",
                                    "{amount}"
                                ], [
                                    $this->plugin->getPrefix(),
                                    ManagerItem::Item()->get($id, $meta)->getName(),
                                    $amount
                                ],
                                $this->plugin->getMessage()['add_trolly']));
                            $soundAPI->addSound($player, 'mob.villager.yes');
                        } else {
                            $chest->onClose($player);
                            $player->sendMessage($chest->translate(
                                [
                                    "{prefix}"
                                ], [
                                    $this->plugin->getPrefix(),
                                ],
                                $this->plugin->getMessage()['trolly_full']));
                            $soundAPI->addSoundType($player, 'closed');
                        }
                    }
                }
            }
        }
    }

    /**
     * @return ChestShop
     */
    private function getChest(): ChestShop {
        if (($type = strtolower($this->config['TypeShop'])) === "single") {
            $inv_menu = InvMenu::create(InvMenu::TYPE_CHEST);
        } elseif ($type === "double") {
            $inv_menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        }
        return new ChestShop($inv_menu, $this->config['Title']);
    }
}