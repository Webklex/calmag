<?php
/*
* File: CalculatorTest.php
* Category: -
* Author: M.Goldenbaum
* Created: 28.12.22 18:11
* Updated: -
*
* Description:
*  -
*/

namespace Tests;

use PHPUnit\Framework\TestCase;
use Webklex\CalMag\Calculator;
use Webklex\CalMag\Config;
use Webklex\CalMag\Enums\GrowState;

class CalculatorTest extends TestCase {

    /**
     * Test data
     *
     * @var array|string[] $data
     */
    protected array $water = [
        "elements" => [
            "calcium" => 61,
            "magnesium" => 4.6,
            "sulphate" => 75,
            "nitrate" => 2.8,
            "nitrite" => 0.01,
        ],
    ];

    protected string $fertilizer = "";
    protected array $additive = [
        "calcium" => "",
        "magnesium" => "",
    ];

    protected float $ratio = 3.5;

    /** @var Calculator $calculator */
    protected Calculator $calculator;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void {
        $this->calculator = new Calculator($this->water, $this->fertilizer, $this->additive, $this->ratio);
    }

    /**
     * CalculatorTest test
     *
     * @return void
     */
    public function testCalculator(): void {
        self::assertSame([
                             "elements" => [
                                 ...$this->water["elements"],
                                 "sulfur" => 25.05,
                                 "nitrogen" => 0.6358400000000001,
                             ],
                         ], $this->calculator->getWater());
        self::assertSame($this->fertilizer, $this->calculator->getFertilizer());
        self::assertSame($this->additive, $this->calculator->getAdditive());
        self::assertSame($this->ratio, $this->calculator->getRatio("calcium"));

        self::expectException(\InvalidArgumentException::class);
        new Calculator(["elements" => []], $this->fertilizer, $this->additive, $this->ratio);
    }

    public function testCalculatorGetElements(): void {
        self::assertSame([
                             'calcium',
                            'humic_acids',
                            'iron',
                            'magnesium',
                            'manganese',
                            'nitrate',
                            'nitrite',
                            'nitrogen',
                            'sulfur',
                            'sulphate',
                            'zinc',
                         ], $this->calculator->getElements());
    }

    public function testCalculatorGetAdditives(): void {
        $additives = $this->calculator->getAdditives();

        self::assertArrayHasKey("calcium", $additives);
        self::assertArrayHasKey("CaCO3", $additives["calcium"]);
        self::assertArrayHasKey("elements", $additives["calcium"]["CaCO3"]);
        self::assertArrayHasKey("calcium", $additives["calcium"]["CaCO3"]["elements"]);
        self::assertSame(40.04, $additives["calcium"]["CaCO3"]["elements"]["calcium"]);
        self::assertArrayHasKey("real", $additives["calcium"]["CaCO3"]);
        self::assertArrayHasKey("calcium", $additives["calcium"]["CaCO3"]["real"]);
        self::assertSame(40.04, $additives["calcium"]["CaCO3"]["real"]["calcium"]);
        self::assertArrayHasKey("concentration", $additives["calcium"]["CaCO3"]);
        self::assertSame(10.0, $additives["calcium"]["CaCO3"]["concentration"]);

        self::assertArrayHasKey("magnesium", $additives);
        self::assertArrayHasKey("MgSO4", $additives["magnesium"]);
        self::assertArrayHasKey("elements", $additives["magnesium"]["MgSO4"]);
        self::assertArrayHasKey("magnesium", $additives["magnesium"]["MgSO4"]["elements"]);
        self::assertArrayHasKey("MgO", $additives["magnesium"]["MgSO4"]["elements"]["magnesium"]);
        self::assertSame(16.0, $additives["magnesium"]["MgSO4"]["elements"]["magnesium"]["MgO"]);
        self::assertArrayHasKey("sulfur", $additives["magnesium"]["MgSO4"]["elements"]);
        self::assertSame(13.0, $additives["magnesium"]["MgSO4"]["elements"]["sulfur"]);
        self::assertArrayHasKey("real", $additives["magnesium"]["MgSO4"]);
        self::assertArrayHasKey("magnesium", $additives["magnesium"]["MgSO4"]["real"]);
        self::assertSame(9.651200000000001, $additives["magnesium"]["MgSO4"]["real"]["magnesium"]);
        self::assertSame(13.0, $additives["magnesium"]["MgSO4"]["real"]["sulfur"]);
        self::assertArrayHasKey("concentration", $additives["magnesium"]["MgSO4"]);
        self::assertSame(10.0, $additives["magnesium"]["MgSO4"]["concentration"]);
    }

    public function testCalculatorGetFertilizers(): void {
        $fertilizers = $this->calculator->getFertilizers();

        self::assertArrayHasKey("BioBizz - CalMag", $fertilizers);
        $fertilizer = $fertilizers["BioBizz - CalMag"];

        self::assertArrayHasKey("name", $fertilizer);
        self::assertSame("CalMag", $fertilizer["name"]);

        self::assertArrayHasKey("elements", $fertilizer);
        $elements = $fertilizer["elements"];

        self::assertArrayHasKey("calcium", $elements);
        self::assertArrayHasKey("CaO", $elements["calcium"]);
        self::assertSame(4.5654, $elements["calcium"]["CaO"]);
        self::assertArrayHasKey("magnesium", $elements);
        self::assertArrayHasKey("MgO", $elements["magnesium"]);
        self::assertSame(1.8478999999999999, $elements["magnesium"]["MgO"]);

        self::assertArrayHasKey("density", $fertilizer);
        self::assertSame(1.087, $fertilizer["density"]);

        self::assertArrayHasKey("ph", $fertilizer);
        self::assertSame(7.0, $fertilizer["ph"]);

        self::assertArrayHasKey("schedule", $fertilizer);
        self::assertSame(false, $fertilizer["schedule"]);

        self::assertArrayHasKey("ratio", $fertilizer);
        self::assertSame(2.9256319238570767, $fertilizer["ratio"]);
    }

    public function testCalculatorGetTargets(): void {
        $targets = $this->calculator->getTargets();

        $states = GrowState::getStates();
        foreach ($states as $state) {
            self::assertArrayHasKey($state->value, $targets);
            self::assertArrayHasKey("weeks", $targets[$state->value]);
            self::assertGreaterThan(0, $targets[$state->value]["weeks"]);
            self::assertArrayHasKey("elements", $targets[$state->value]);
            self::assertArrayHasKey("calcium", $targets[$state->value]["elements"]);
            self::assertArrayHasKey("magnesium", $targets[$state->value]["elements"]);
            self::assertGreaterThan(0, $targets[$state->value]["elements"]["calcium"]);
            self::assertGreaterThan(0, $targets[$state->value]["elements"]["magnesium"]);
        }
    }

    public function testCalculatorSetTarget(): void {
        $targets = $this->calculator->getTargets();
        $this->calculator->setTarget(GrowState::Flower, [
            "elements" => [
                "calcium" => 56,
                "magnesium" => 10,
            ],
        ]);
        $_targets = $this->calculator->getTargets();
        self::assertArrayHasKey(GrowState::Flower->value, $_targets);
        $target = $_targets[GrowState::Flower->value];
        self::assertArrayHasKey("elements", $target);
        self::assertArrayHasKey("calcium", $target["elements"]);
        self::assertSame(56, $target["elements"]["calcium"]);
        self::assertArrayHasKey("magnesium", $target["elements"]);
        self::assertSame(10, $target["elements"]["magnesium"]);

        $this->calculator->setTarget(GrowState::Flower, [
            "elements" => [
                "calcium" => 56,
            ],
        ]);
        $_targets = $this->calculator->getTargets();
        self::assertArrayHasKey(GrowState::Flower->value, $_targets);
        $target = $_targets[GrowState::Flower->value];
        self::assertArrayHasKey("elements", $target);
        self::assertArrayHasKey("calcium", $target["elements"]);
        self::assertSame(56, $target["elements"]["calcium"]);
        self::assertArrayHasKey("magnesium", $target["elements"]);
        self::assertSame(16.0, $target["elements"]["magnesium"]);

        $this->calculator->setTarget(GrowState::Flower, [
            "elements" => [
                "magnesium" => 17,
            ],
        ]);
        $_targets = $this->calculator->getTargets();
        self::assertArrayHasKey(GrowState::Flower->value, $_targets);
        $target = $_targets[GrowState::Flower->value];
        self::assertArrayHasKey("elements", $target);
        self::assertArrayHasKey("calcium", $target["elements"]);
        self::assertSame(59.5, $target["elements"]["calcium"]);
        self::assertArrayHasKey("magnesium", $target["elements"]);
        self::assertSame(17, $target["elements"]["magnesium"]);

        foreach($targets as $state => $target) {
            $this->calculator->setTarget(GrowState::fromString($state), $target);
        }
    }


    public function testCalculatorGetFertilizer(): void {
        $fertilizer = $this->calculator->getFertilizer();
        self::assertSame("", $fertilizer);

        $this->calculator->setFertilizer("BioBizz - CalMag");
        $fertilizer = $this->calculator->getFertilizer();
        self::assertSame("BioBizz - CalMag", $fertilizer);

        $this->calculator->setFertilizer("");
    }


    public function testCalculatorGetAdditive(): void {
        $additive = $this->calculator->getAdditive();
        self::assertArrayHasKey("calcium", $additive);
        self::assertArrayHasKey("magnesium", $additive);
        self::assertSame("", $additive["calcium"]);
        self::assertSame("", $additive["magnesium"]);

        $this->calculator->setAdditive([
            "calcium" => "CaCO3",
            "magnesium" => "MgSO4",
                                       ], ["calcium" => 2.0, "magnesium" => 2.0]);
        $_additive = $this->calculator->getAdditive();
        self::assertArrayHasKey("calcium", $_additive);
        self::assertArrayHasKey("magnesium", $_additive);
        self::assertSame("CaCO3", $_additive["calcium"]);
        self::assertSame("MgSO4", $_additive["magnesium"]);

        $this->calculator->setAdditive($additive, ["calcium" => 0.0, "magnesium" => 0.0]);
    }

    public function testCalculatorGetAdditiveComponents(): void {
        $additive = $this->calculator->getAdditive();
        $this->calculator->setAdditive([
                                           "calcium" => "CaCO3",
                                           "magnesium" => "MgSO4",
                                       ], ["calcium" => 2.0, "magnesium" => 2.0]);

        $elements = $this->calculator->getAdditiveComponents("calcium", 1.0);
        self::assertArrayHasKey("calcium", $elements);
        self::assertArrayHasKey("magnesium", $elements);
        self::assertSame(8.008, $elements["calcium"]);
        self::assertSame(0.0, $elements["magnesium"]);

        $elements = $this->calculator->getAdditiveComponents("magnesium", 1.0);
        self::assertArrayHasKey("calcium", $elements);
        self::assertArrayHasKey("magnesium", $elements);
        self::assertSame(0.0, $elements["calcium"]);
        self::assertSame(1.93024, $elements["magnesium"]);

        $this->calculator->setAdditive($additive, ["calcium" => 0.0, "magnesium" => 0.0]);
    }

    public function testCalculatorGetFertilizerComponents(): void {
        $fertilizer = $this->calculator->getFertilizer();
        self::assertSame("", $fertilizer);

        $this->calculator->setFertilizer("BioBizz - CalMag");
        $fertilizer = $this->calculator->getFertilizer();
        self::assertSame("BioBizz - CalMag", $fertilizer);

        $elements = $this->calculator->getFertilizerComponents(1.0);
        self::assertArrayHasKey("calcium", $elements);
        self::assertArrayHasKey("magnesium", $elements);
        self::assertSame(32.610652200000004, $elements["calcium"]);
        self::assertSame(11.146532799999997, $elements["magnesium"]);

        $this->calculator->setFertilizer("");
    }

    public function testCalculatorGetWater(): void {
        $water = $this->calculator->getWater();
        self::assertArrayHasKey("elements", $water);
        /** @var array $elements */
        $elements = $water["elements"];
        self::assertArrayHasKey("calcium", $elements);
        self::assertArrayHasKey("magnesium", $elements);
        self::assertArrayHasKey("sulphate", $elements);
        self::assertArrayHasKey("nitrate", $elements);
        self::assertArrayHasKey("nitrite", $elements);
        self::assertSame(61, $elements["calcium"]);
        self::assertSame(4.6, $elements["magnesium"]);
        self::assertSame(75, $elements["sulphate"]);
        self::assertSame(2.8, $elements["nitrate"]);
        self::assertSame(0.01, $elements["nitrite"]);
        self::assertSame(25.05, $elements["sulfur"]);
        self::assertSame(0.6358400000000001, $elements["nitrogen"]);

        $this->calculator->setWater([
                                        "elements" => [
                                            "calcium" => 50,
                                            "magnesium" => 5,
                                            "sulphate" => 80,
                                            "nitrate" => 3,
                                            "nitrite" => 0.02,
                                        ],
                                    ]);
        $_water = $this->calculator->getWater();
        self::assertArrayHasKey("elements", $_water);
        /** @var array $elements */
        $elements = $_water["elements"];
        self::assertArrayHasKey("calcium", $elements);
        self::assertArrayHasKey("magnesium", $elements);
        self::assertArrayHasKey("sulphate", $elements);
        self::assertArrayHasKey("nitrate", $elements);
        self::assertArrayHasKey("nitrite", $elements);
        self::assertSame(50, $elements["calcium"]);
        self::assertSame(5, $elements["magnesium"]);
        self::assertSame(80, $elements["sulphate"]);
        self::assertSame(3, $elements["nitrate"]);
        self::assertSame(0.02, $elements["nitrite"]);
        self::assertSame(26.720000000000002, $elements["sulfur"]);
        self::assertSame(0.68408, $elements["nitrogen"]);


        $this->calculator->setAdditive([
                                           "calcium" => "CaCO3",
                                           "magnesium" => "MgSO4",
                                       ], ["calcium" => 2.0, "magnesium" => 2.0]);
        $this->calculator->setFertilizer("BioBizz - CalMag");
        $this->calculator->setWater([
                                        "elements" => [
                                            "calcium" => 50,
                                            "magnesium" => 5,
                                            "sulphate" => 80,
                                            "nitrate" => 3,
                                            "nitrite" => 0.02,
                                        ],
                                    ]);
        $_water = $this->calculator->getWater();
        self::assertArrayHasKey("elements", $_water);
        /** @var array $elements */
        $elements = $_water["elements"];
        self::assertArrayHasKey("calcium", $elements);
        self::assertArrayHasKey("magnesium", $elements);
        self::assertArrayHasKey("sulfur", $elements);
        self::assertArrayNotHasKey("sulphate", $elements);
        self::assertArrayNotHasKey("nitrate", $elements);
        self::assertArrayNotHasKey("nitrite", $elements);
        self::assertArrayNotHasKey("nitrogen", $elements);
        self::assertSame(50, $elements["calcium"]);
        self::assertSame(5, $elements["magnesium"]);
        self::assertSame(26.720000000000002, $elements["sulfur"]);

        $this->calculator->setWater($this->water);
    }

    public function testCalculatorSetRatio(): void {
        $ca_ratio = $this->calculator->getRatio("calcium");
        $mg_ratio = $this->calculator->getRatio("magnesium");

        self::assertSame(3.5, $ca_ratio);
        self::assertSame(1.0, $mg_ratio);

        $this->calculator->setRatio(4.0, 1.0);
        self::assertSame(4.0, $this->calculator->getRatio("calcium"));
        self::assertSame(1.0, $this->calculator->getRatio("magnesium"));

        $this->calculator->setRatio($ca_ratio, $mg_ratio);
    }

    public function testCalculatorGetDeficiencyRatio(): void {
        $deficiency = $this->calculator->getDeficiencyRatio();
        self::assertArrayHasKey("calcium", $deficiency);
        self::assertArrayHasKey("magnesium", $deficiency);
        self::assertSame(13.260869565217392, $deficiency["calcium"]);
        self::assertSame(1.0, $deficiency["magnesium"]);

        $this->calculator->setWater([
                                        "elements" => [
                                            "calcium" => 10,
                                            "magnesium" => 50,
                                        ],
                                    ]);
        $deficiency = $this->calculator->getDeficiencyRatio();
        self::assertArrayHasKey("calcium", $deficiency);
        self::assertArrayHasKey("magnesium", $deficiency);
        self::assertSame(1.0, $deficiency["calcium"]);
        self::assertSame(5, $deficiency["magnesium"]);

        $this->calculator->setWater([
                                        "elements" => [
                                            "calcium" => 10,
                                            "magnesium" => 10,
                                        ],
                                    ]);
        $deficiency = $this->calculator->getDeficiencyRatio();
        self::assertArrayHasKey("calcium", $deficiency);
        self::assertArrayHasKey("magnesium", $deficiency);
        self::assertSame(1.0, $deficiency["calcium"]);
        self::assertSame(1.0, $deficiency["magnesium"]);

        $this->calculator->setWater($this->water);
    }

    public function testCalculatorGetSuggestedAdditives(): void {
        $additive = $this->calculator->getAdditive();

        $this->calculator->setAdditive([
                                           "calcium" => "CaCO3",
                                           "magnesium" => "MgSO4",
                                       ], ["calcium" => 2.0, "magnesium" => 2.0]);

        $suggestions = $this->calculator->getSuggestedAdditives([
                                                                    "calcium" => 43.7,
                                                                    "magnesium" => 17.5,
                                                                ]);
        self::assertArrayHasKey("calcium", $suggestions);
        self::assertArrayHasKey("magnesium", $suggestions);
        $ca_additive = $suggestions["calcium"];
        $mg_additive = $suggestions["magnesium"];
        self::assertArrayHasKey("missing", $ca_additive);
        self::assertArrayHasKey("missing", $mg_additive);
        self::assertSame(43.7, $ca_additive["missing"]);
        self::assertSame(17.5, $mg_additive["missing"]);
        self::assertSame(10.914085914085915, $ca_additive["concentration"]);
        self::assertSame(18.132460212201593, $mg_additive["concentration"]);
        self::assertSame("CaCO3", $ca_additive["additive"]);
        self::assertSame("MgSO4", $mg_additive["additive"]);
        self::assertSame(1.0, $ca_additive["ml"]);
        self::assertSame(1.0, $mg_additive["ml"]);
        self::assertSame(40.04, $ca_additive["elements"]["calcium"]);
        self::assertSame(16.0, $mg_additive["elements"]["magnesium"]["MgO"]);
        self::assertSame(13.0, $mg_additive["elements"]["sulfur"]);
        self::assertSame(43.7, $ca_additive["real"]["calcium"]);
        self::assertSame(0.0, $mg_additive["real"]["calcium"]);
        self::assertSame(0.0, $ca_additive["real"]["magnesium"]);
        self::assertSame(17.5, $mg_additive["real"]["magnesium"]);
        self::assertSame(23.57219827586207, $mg_additive["real"]["sulfur"]);

        $this->calculator->setAdditive($additive, ["calcium" => 0.0, "magnesium" => 0.0]);
    }

    public function testCalculatorSetFertilizer(): void {
        $fertilizer = $this->calculator->getFertilizer();

        self::expectException(\InvalidArgumentException::class);
        $this->calculator->setFertilizer("Unknown Fertilizer");

        $this->calculator->setFertilizer("BioBizz - CalMag");
        self::assertSame("BioBizz - CalMag", $this->calculator->getFertilizer());

        $this->calculator->setFertilizer($fertilizer);
    }

    public function testCalculatorSetAdditive(): void {
        $additive = $this->calculator->getAdditive();

        self::expectException(\InvalidArgumentException::class);

        $this->calculator->setAdditive([
                                           "calcium" => "Unknown",
                                           "magnesium" => "MgSO4",
                                       ], ["calcium" => 2.0, "magnesium" => 2.0]);

        self::expectException(\InvalidArgumentException::class);

        $this->calculator->setAdditive([
                                           "calcium" => "CaCO3",
                                           "magnesium" => "Unknown",
                                       ], ["calcium" => 2.0, "magnesium" => 2.0]);

        $this->calculator->setAdditive($additive, ["calcium" => 0.0, "magnesium" => 0.0]);
    }

    public function testCalculatorCalculateFertilizer(): void {
        $additive = $this->calculator->getAdditive();
        $fertilizer = $this->calculator->getFertilizer();

        $this->calculator->setAdditive([
                                           "calcium" => "CaCO3",
                                           "magnesium" => "MgSO4",
                                       ], ["calcium" => 2.0, "magnesium" => 2.0]);
        $this->calculator->setFertilizer("BioBizz - CalMag");

        $result = $this->calculator->calculateFertilizer([
            "elements" => [
                "calcium" => 50,
                "magnesium" => 5,
            ],
        ]);
        self::assertArrayHasKey("suggested_additive", $result);
        self::assertArrayHasKey("elements", $result);
        self::assertSame(3.6683349889842307, $result["ratio"]);
        self::assertSame(0.07879761995099636, $result["dilution"]);
        self::assertSame(0.9212023800490037, $result["water"]);

        self::assertSame([
            "ml" => 0.42,
            "name" => "BioBizz - CalMag",
                         ], $result["fertilizer"]);
        self::assertSame([
                             "ml" => 0,
                             "mg" => 0.0,
                             "name" => "CaCO3",
                             "concentration" => 2.0,
                         ], $result["additive"]["calcium"]);
        self::assertSame([
                             'ml' => 5.44,
                             'mg' => 108.80000000000001,
                             "name" => "MgSO4",
                             "concentration" => 2.0,
                         ], $result["additive"]["magnesium"]);

        self::assertSame([
            "elements" => [
                "calcium" => 50,
                "magnesium" => 5,
            ],
                         ], $result["target"]);
        self::assertSame([
            "calcium" => -11,
            "magnesium" => 0.40000000000000036,
                         ], $result["missing"]);

        self::assertSame([
                             'calcium' => 18.503128741010777,
    'magnesium' => 5.044012827774578,
    'sulphate' => 5.909821496324727,
    'nitrate' => 0.2206333358627898,
    'nitrite' => 0.0007879761995099637,
    'sulfur' => 1.973880379772459,
    'nitrogen' => 0.050102678669641534,
                         ], $result["elements"]);


        $this->calculator->setWater([
                                        "elements" => [
                                            "calcium" => 0,
                                            "magnesium" => 0,
                                        ],
                                    ]);
        $this->calculator->setAdditive([
                                           "calcium" => "CaCO3",
                                           "magnesium" => "MgSO4",
                                       ], ["calcium" => 2.0, "magnesium" => 2.0]);
        $this->calculator->setFertilizer("Atami - ATA CalMag");

        $result = $this->calculator->calculateFertilizer([
                                                             "elements" => [
                                                                 "calcium" => 50,
                                                                 "magnesium" => 5,
                                                             ],
                                                         ]);
        self::assertArrayHasKey("suggested_additive", $result);
        self::assertArrayHasKey("elements", $result);
        self::assertSame(3.5016294216294166, $result["ratio"]);
        self::assertSame(1.0, $result["dilution"]);
        self::assertSame(0.0, $result["water"]);

        $this->calculator->setWater($this->water);
        $this->calculator->setFertilizer($fertilizer);
        $this->calculator->setAdditive($additive, ["calcium" => 0.0, "magnesium" => 0.0]);
    }

    public function testCalculatorSummarizeElements(): void {
        $elements = $this->calculator->summarizeElements([
                                                             "calcium" => [
                                                                    "CaO" => 4.2,
                                                             ],
                                                             "magnesium" => [
                                                                 "MgO" => 1.7,
                                                             ],
                                                             "nitrogen" => [
                                                                    "N" => 1.0,
                                                                    "organic" => 5.0,
                                                             ],
                                                         ]);
        self::assertArrayHasKey("calcium", $elements);
        self::assertArrayHasKey("magnesium", $elements);
        self::assertArrayHasKey("nitrogen", $elements);
        self::assertSame(3.0000600000000004, $elements["calcium"]);
        self::assertSame(1.02544, $elements["magnesium"]);
        self::assertSame(6.0, $elements["nitrogen"]);
    }

    public function testCalculatorGetAppliedFertilizer(): void {
        $result = $this->calculator->getAppliedFertilizer();
        foreach(GrowState::getStates() as $state) {
            self::assertArrayHasKey($state->value, $result);
        }
    }

    public function testCalculatorGenerateResultTable(): void {
        $additive = $this->calculator->getAdditive();
        $fertilizer = $this->calculator->getFertilizer();

        $this->calculator->setAdditive([
                                           "calcium" => "CaCO3",
                                           "magnesium" => "MgSO4",
                                       ], ["calcium" => 2.0, "magnesium" => 2.0]);
        $this->calculator->setFertilizer("BioBizz - CalMag");

        $table = $this->calculator->generateResultTable();
        self::assertArrayHasKey("targets", $table);
        self::assertArrayHasKey("fertilizer", $table);
        self::assertArrayHasKey("ca_additive", $table);
        self::assertArrayHasKey("mg_additive", $table);
        self::assertArrayHasKey("elements", $table);
        self::assertArrayHasKey("water", $table);
        self::assertArrayHasKey("ratio", $table);
        self::assertArrayHasKey("target", $table);
        self::assertArrayHasKey("missing", $table);
        self::assertArrayHasKey("suggested", $table);

        $targets = $table["targets"];
        foreach(GrowState::getStates() as $state) {
            self::assertArrayHasKey($state->value, $targets);
            $target = $targets[$state->value];
            self::assertArrayHasKey("weeks", $target);
            self::assertArrayHasKey("elements", $target);
            self::assertArrayHasKey("calcium", $target["elements"]);
            self::assertArrayHasKey("magnesium", $target["elements"]);
            self::assertGreaterThan(0, $target["weeks"]);
            self::assertGreaterThan(0, $target["elements"]["calcium"]);
            self::assertGreaterThan(0, $target["elements"]["magnesium"]);
        }

        $_fertilizer = $table["fertilizer"];
        self::assertArrayHasKey("rows", $_fertilizer);
        self::assertSame(12, count($_fertilizer["rows"]));
        self::assertSame("BioBizz - CalMag", $_fertilizer["name"]);
        self::assertSame(12.129999999999999, array_sum($_fertilizer["rows"]));

        $ca_additive = $table["ca_additive"];
        self::assertArrayHasKey("rows", $ca_additive);
        self::assertSame(12, count($ca_additive["rows"]));
        self::assertSame("CaCO3", $ca_additive["name"]);
        self::assertSame(0, array_sum(array_map(function($row) {
            return $row["ml"];
        }, $ca_additive["rows"])));

        $mg_additive = $table["mg_additive"];
        self::assertArrayHasKey("rows", $mg_additive);
        self::assertSame(12, count($mg_additive["rows"]));
        self::assertSame("MgSO4", $mg_additive["name"]);
        self::assertSame(68.09, array_sum(array_map(function($row) {
            return $row["ml"];
        }, $mg_additive["rows"])));

        $target = $table["target"];
        self::assertSame(12, count($target));
        foreach($target as $week => $_target) {
            self::assertArrayHasKey("elements", $_target);
            self::assertArrayHasKey("state", $_target);
            self::assertArrayHasKey("state", $_target);
            self::assertArrayHasKey("weeks", $_target);
            self::assertArrayHasKey("calcium", $_target["elements"]);
            self::assertArrayHasKey("magnesium", $_target["elements"]);
            self::assertGreaterThan(0, $_target["weeks"]);
            self::assertGreaterThan(0, $_target["elements"]["calcium"]);
            self::assertGreaterThan(0, $_target["elements"]["magnesium"]);
        }

        $elements = $table["elements"];
        self::assertSame(12, count($elements));
        foreach($elements as $week => $components) {
            $_target = $target[$week];
            foreach($_target["elements"] as $element => $value) {
                self::assertArrayHasKey($element, $components);
                self::assertEqualsWithDelta($components[$element], $value, $value*0.03);
            }
        }

        $water = $table["water"];
        self::assertSame(12, count($water));
        self::assertSame(0.016393442622950838, array_sum(array_map(function($row) {
            return $row["water"];
        }, $water)));
        self::assertSame(11.98360655737705, array_sum(array_map(function($row) {
            return $row["dilution"];
        }, $water)));

        $ratio = $table["ratio"];
        self::assertSame(12, count($ratio));
        foreach($ratio as $week => $value) {
            self::assertEqualsWithDelta($value, $this->ratio, $this->ratio*0.03);
        }

        $missing = $table["missing"];
        self::assertSame(12, count($missing));
        foreach($missing as $week => $_elements) {
            self::assertArrayHasKey("calcium", $_elements);
            self::assertArrayHasKey("magnesium", $_elements);
        }

        $suggested = $table["suggested"];
        self::assertSame(12, count($suggested));

        $this->calculator->setFertilizer($fertilizer);
        $this->calculator->setAdditive($additive, ["calcium" => 0.0, "magnesium" => 0.0]);
    }


    public function testCalculatorCalculate(): void {
        $result = $this->calculator->calculate();
        self::assertArrayHasKey("deficiency", $result);
        self::assertArrayHasKey("results", $result);
        self::assertArrayHasKey("table", $result);
    }
}