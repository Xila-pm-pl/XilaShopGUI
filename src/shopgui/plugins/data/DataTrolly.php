<?php



declare(strict_types = 1);

namespace shopgui\plugins\data;



use pocketmine\utils\Config;

use shopgui\plugins\Loader;



class DataTrolly {



    public function __construct(private Loader $plugin, private Config $data_config) {

    }



    /**

     * @return Config

     */

    public function getDataTrolly(): Config {

        return $this->data_config;

    }



    /**

     * @param string $player_name

     * @return void

     */

    public function createDataTrolly(string $player_name): void {

        $this->data_config->set($player_name, self::formatData());

        $this->saveData($this->data_config);

    }



    /**

     * @param string $player_name

     * @return boolean

     */

    public function hasDataTrolly(string $player_name): bool {

        return ($this->data_config->exists($player_name)) ? true : false;

    }



    /**

     * @param string $player_name

     * @return array

     */

    public function getAllData(string $player_name): array {

        return $this->data_config->getAll()[$player_name];

    }



    /**

     * @param string $player_name

     * @return array

     */

    public function getTrolly(string $player_name): array {

        return $this->data_config->getAll()[$player_name]["Trolly"];

    }



    /**

     * @param string $player_name

     * @return integer

     */

    public function getTotalPrice(string $player_name): int {

        return (int)$this->data_config->getAll()[$player_name]["Total_price"];

    }



    /**

     * @param string $player_name

     * @param string $data_addtrolly

     * @return void

     */

    public function addTrolly(string $player_name, string $data_addtrolly) {

        $newTrolly = $this->data_config->getNested(($format = $player_name.".Trolly"));

        $newTrolly[] = $data_addtrolly;



        $this->data_config->setNested($player_name.".Total_price", ($this->getTotalPrice($player_name) + (int)explode('--', $data_addtrolly)[1]));

        $this->data_config->setNested($format, $newTrolly);

        $this->saveData($this->data_config);

    }



    /**

     * @param string $player_name

     * @param integer $slots_deltrolly

     * @param string $data_deltrolly

     * @return void

     */

    public function delTrolly(string $player_name, int $slots_deltrolly) {

        $dataTrolly = $this->data_config->getNested(($format = $player_name.".Trolly"));

        $delTrolly = [];

        foreach ($dataTrolly as $slots => $datas) {

            if ($slots !== $slots_deltrolly) {

                $delTrolly[] = $datas;

            }

            if ($slots === $slots_deltrolly) {

                $price = explode('--', $datas)[1];

            }

        }



        $this->data_config->setNested($player_name.".Total_price", ($this->getTotalPrice($player_name) - (int)$price));

        $this->data_config->setNested($format, $delTrolly);

        $this->saveData($this->data_config);

    }



    /**

     * @param string $player_name

     * @return void

     */

    public function delAllTrolly(string $player_name) {

        $this->data_config->setNested($player_name.".Total_price", 0);

        $this->data_config->setNested($player_name.".Trolly", []);

        $this->saveData($this->data_config);

    }



    /**

     * @param Config $data_config

     * @return void

     */

    private function saveData(Config $data_config): void {

        $data_config->save();

        $data_config->reload();

    }



    /**

     * @return array

     */

    private static function formatData(): array {

        return [

            "Total_price" => 0,

            "Trolly" => []

        ];

    }

}
