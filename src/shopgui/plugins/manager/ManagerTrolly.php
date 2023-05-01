<?php declare(strict_types = 1);
namespace shopgui\plugins\manager;

use AriefaaL\quest\events\PlayerBuyEvent;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use shopgui\plugins\data\DataTrolly;
use shopgui\plugins\inventory\ChestTrolly;
use shopgui\plugins\Loader;

class ManagerTrolly {

    public function __construct(private Loader $plugin, private DataTrolly $data_trolly) {
    }

    /**
     * @param Player $player
     * @return void
     */
    public function openTrolly(Player $player) {
        $chest = new ChestTrolly(InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST), $this->plugin->getConfigs()['Message']['title_trolly']);
        
        $myMoney = null;
        if (($ecoApi = $this->plugin->economyAPI) !== null) {
            if ($this->plugin->economyValue !== false) {
                $myMoney = (string)$ecoApi->myMoney($player);
            }
        }
        $chest->setItems($this->data_trolly->getTrolly($player->getName()));
        $chest->setOutLine($this->data_trolly->getAllData($player->getName()), $myMoney);

        $chest->setListener(function(InvMenuTransaction $transaction) use ($chest) : InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $itemClicked = $transaction->getItemClicked();
            $action = $transaction->getAction();

            $soundAPI = $this->plugin->getSound();

            if ($chest->itemClicked("confirm", $itemClicked)) {
                // Confim buy all
                if (count($dataTrolly = $this->data_trolly->getTrolly($player->getName())) > 0) {
                    $price = $this->data_trolly->getTotalPrice($player->getName());
                    $amountT = count($dataTrolly);

                    if (($ecoApi = $this->plugin->economyAPI) !== null) {
                        if ($this->plugin->economyValue !== false) {
                            if ($ecoApi->myMoney($player) >= $price) {
                                $ecoApi->reduceMoney($player, $price); 
                            } else {
                                $player->sendMessage($chest->translate(
                                    [
                                        "{prefix}",
                                        "{total_price}",
                                        "{my_money}"
                                    ], [
                                        $this->plugin->getPrefix(),
                                        $price,
                                        $ecoApi->myMoney($player)
                                    ],
                                    $this->plugin->getMessage()['buy_not_money']));
                                $soundAPI->addSound($player, 'mob.villager.no');
                                return $transaction->discard();
                            }
                        }
                    }

                    foreach ($dataTrolly as $slots => $data) {
                        $array = explode('--', $data);
                        $items = explode(':', $array[0]);
                        $packet = ManagerItem::Item()->get((int)$items[0], (int)$items[1], (int)$array[2]);

                        if ($player->getInventory()->canAddItem($packet)) {
                            $player->getInventory()->addItem($packet);
                        } else {
                            $player->getWorld()->dropItem($player->getPosition(), $packet);
                        }

                        if ($this->plugin->getServer()->getPluginManager()->getPlugin("QuestTime") !== null) {
                            // (new PlayerBuyEvent($player, (int)$array[2]))->call();
                        }
                    }

                    $chest->onClose($player);
                    
                    $player->sendMessage($chest->translate(
                        [
                            "{prefix}",
                            "{total_item}",
                            "{total_price}"
                        ], [
                            $this->plugin->getPrefix(),
                            $amountT,
                            $price
                        ],
                        $this->plugin->getMessage()['buy_success']));
                    $this->data_trolly->delAllTrolly($player->getName());
                    $soundAPI->addSoundType($player, 'level');
                } else {
                    $soundAPI->addSoundType($player, 'pop1');
                }
            } elseif ($chest->itemClicked("cancel", $itemClicked)) {
                // Cancle buy all
                if (count($this->data_trolly->getTrolly($player->getName())) > 0) {
                    $this->data_trolly->delAllTrolly($player->getName());
                    $chest->onClose($player);
                    $this->openTrolly($player);
                    $soundAPI->addSound($player, 'mob.villager.no');
                } else {
                    $soundAPI->addSoundType($player, 'pop1');
                }
            } elseif ($chest->itemClicked("outline", $itemClicked)) {
                $soundAPI->addSoundType($player, 'pop2');
            } else {
                foreach ($this->data_trolly->getTrolly($player->getName()) as $slots => $data_trolly) {
                    if ((int)$slots === $action->getSlot()) {
                        $this->data_trolly->delTrolly($player->getName(), (int)$slots);
                        $chest->onClose($player);
                        $this->openTrolly($player);
                        $soundAPI->addSound($player, 'mob.villager.no');
                    }
                }
            }

            return $transaction->discard();
        });
        
        $chest->setInventoryCloseListener(function(Player $player, Inventory $inventory) {
            if ($this->plugin->getConfigs()['FormUI'] !== false) {
                $this->plugin->getForm()->sendForm($player);
            } else {
            	$this->plugin->getSound()->addSoundType($player, 'closed');
            }
        });

        $chest->sendTo($player);
    }
}