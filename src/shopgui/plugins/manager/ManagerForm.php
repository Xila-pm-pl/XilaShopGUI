<?php declare(strict_types=1);

namespace shopgui\plugins\manager;



use pocketmine\player\Player;

use pocketmine\utils\TextFormat as TF;

use shopgui\library\form\SimpleForm;

use shopgui\plugins\Loader;

use shopgui\plugins\utils\Translate;



class ManagerForm {



    use Translate;



    /** @var string */

    private const TITLE = TF::DARK_RED."Pixel Shop";

    /** @var string */

    private const CONTENT = TF::WHITE."Do you wanna buy something items?";

    /** @var string */

    private const TROLLY = TF::BOLD."Your Trolly";

    /** @var string */

    private const NOTHING = TF::DARK_RED."NOTHING FOR BUY";

    /** @var integer */

    private const IMAGE_TYPE_PATH = 0;

    /** @var integer */

    private const IMAGE_TYPE_URL = 1;



    public function __construct(private Loader $plugin) {

    }



    public function sendForm(Player $player): void {

        $form = $this->getForms($player);

        $form->sendToPlayer($player);

    }



    /**

     * @param SimpleForm $form

     * @return void

     */

    private function title(SimpleForm $form) {

        return $form->setTitle(self::TITLE);

    }



    /**

     * @param SimpleForm $form

     * @return void

     */

    private function content(SimpleForm $form) {

        return $form->setContent(self::CONTENT);

    }



    /**

     * @param SimpleForm $form

     * @return void

     */

    private function button(SimpleForm $form): void {

        if (count(($datas = $this->plugin->getConfigs()['LoadShops'])) > 0) {

            foreach ($datas as $data => $nameShop) {

                if (file_exists($this->plugin->getDataFolder()."shops/".$nameShop.".yml")) {

                    $imgType = -1;

                    if (($imgPath = $this->plugin->getDataShop($nameShop)->getAll()['Image']) !== "") {

                        switch (explode('/', $imgPath)[0]) {

                            case 'textures':

                                $imgType = self::IMAGE_TYPE_PATH;

                                break;

                            case 'http:':

                            case 'https:':

                                $imgType = self::IMAGE_TYPE_URL;

                                break;

                            default:

                                $imgType = -1;

                        }

                    }

                    $form->addButton($this->buttonText(TF::BOLD.strtoupper($nameShop)), $imgType, $imgPath, (string)$nameShop);

                }

            }

        } else {

            $form->addButton($this->buttonText(self::NOTHING), -1, "", "nothing");

        }

    }



    /**

     * @param Player $player

     * @return SimpleForm

     */

    private function getForms(Player $player): SimpleForm {

        $form = new SimpleForm(function(Player $player, $data = null) {

            if ($data === null) return;

            if ($data === "nothing") {

                $player->sendMessage($this->translate(

                    [

                        "{prefix}"

                    ], [

                        $this->plugin->getPrefix()

                    ],

                    $this->plugin->getMessage()['nothing_items']));

            } elseif ($data === "trolly") {

                $trolly = $this->plugin->getTrolly();

                $trolly->openTrolly($player);

                $this->plugin->getSound()->addSoundType($player, 'open');

            } else {

                $shop = $this->plugin->getShop($data);

                $shop->openShop($player);

                $this->plugin->getSound()->addSoundType($player, 'open');

            }

            return;

        });



        $this->title($form);

        $this->content($form);

        $count = count($this->plugin->getDataTrolly()->getTrolly($player->getName()));

        $form->addButton(self::TROLLY."\n".TF::RESET.TF::GRAY."Have ".TF::GREEN.$count.TF::GRAY." items in trolly", 0, "", "trolly");

        $this->button($form);

        return $form;

    }



    /**

     * @param string $text

     * @return string

     */

    private function buttonText(string $line): string {

        return implode("\n".TF::RESET, [$line, TF::GRAY."Click to open"]);

    }

}
