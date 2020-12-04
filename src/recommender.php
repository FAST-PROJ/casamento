<?php
/**
 * @copyright 2020
 * @author    Vinicius Alves <vinicius_o.a@live.com>
 * @package   FAST_PROJ
 * @since     2020-11-30
*/

require 'features.php';

/**
 * Classe responsável por filtrar o dataset de usuários de casamentos
 */
class WeddingFinder
{
    /**
     * Nome da da feature de classe do dataset
     */
    const CLASSE = 'classificacao';

    /**
     * Identificador do usuário filtrado
     */
    const USER_FILTERING = 'user_filtering_identifier';

    /**
     * Guarda o handler do dataset
     *
     * @var resource
     */
    protected $dataset;
    
    /**
     * Guarda as features do usuário a ser pesquisado
     *
     * @var array
     */
    protected $features;

    /**
     * Constructor
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->dataset($filename);
    }

    /**
     * Faz a leitura do dataset
     *
     * @param string $filename
     * @return void
     */
    protected function dataset($filename)
    {
        $this->dataset = fopen($filename, 'r');
        $header = $this->getDatasetHeader();
        $this->read($header);
    }

    /**
     * Retorna o header do dataset
     *
     * @return array
     */
    protected function getDatasetHeader()
    {
        return fgetcsv($this->dataset, 1000);
    }

    /**
     * Read dataset csv
     *
     * @param array $header
     * @return void
     */
    protected function read($header)
    {
        while ($line = fgetcsv($this->dataset)) {
            $this->features[] = array_combine($header, $line);
        }
    }

    /**
     * Retorna o score das features dos usuários
     *
     * @param string $firstUser
     * @param string $secondUser
     * @return array
     */
    protected function getFeatures($firstUser, $secondUser)
    {
        $tuple = [];
        array_map(function($data1, $data2) use (&$tuple) {
            array_push($tuple, array($data1, $data2));
        }, $this->features[$firstUser], $this->features[$secondUser]);

        return $tuple;
    }

    /**
     * Faz o calculo da distancia euclidiana entre as features dos usuários
     *
     * @param array $points
     * @return float
     */
    protected function euclideanSimilarity(array $points)
    {
        $tuple = [];
        array_map(function($points) use (&$tuple) {
            array_push($tuple, pow($points[0] - $points[1], 2));
        }, $points);

        return 1 / (1 + sqrt(array_sum($tuple)));
    }

    /**
     * Calculo o score do dataset dos usuários
     *
     * @param string $user
     * @return array
     */
    protected function calculateUserScores($user, $qtSuggestions)
    {
        $tuple = [];
        foreach (array_keys($this->features) as $otherUser) {
            if ($otherUser == $user) {
                continue;
            }

            array_push(
                $tuple,
                [
                    $otherUser => $this->getFeatureSimilarity(
                        $user,
                        $otherUser
                    )
                ]
            );
        }

        uasort($tuple, function($first, $second) {
            if (reset($first) == reset($second)) {
                return 0;
            }

            return (reset($first) < reset($second)) ? 1 : -1;
        });

        $tuple = array_slice($tuple, 0, $qtSuggestions);
        return $tuple;
    }

    /**
     * Retorna as recomendações de usuários do dataset
     *
     * @param array $recommendations
     * @return array
     */
    protected function getRecommendations(array $recommendations)
    {
        $recommend = [];
        array_map(function($recommendations) use (&$recommend) {
            if (!isset($recommend[key($recommendations)])) {
                $recommend[key($recommendations)] = 1;
                return;
            }

            $recommend[key($recommendations)] += 1;
            return;
        }, $recommendations);

        krsort($recommend);
        $user = key($recommend);

        return [
            'user'     => $user,
            'features' => $this->features[$user],
            'class'    => $this->features[$user][self::CLASSE]
        ];
    }

    /**
     * Retorna a similaridade entre as features dos usuários
     *
     * @param string $firstUser
     * @param string $secondUser
     * @return void
     */
    public function getFeatureSimilarity($firstUser, $secondUser)
    {
        return $this->euclideanSimilarity(
            $this->getFeatures($firstUser, $secondUser)
        );
    }

    /**
     * Add um usuário na lista de usuarios do dataset
     *
     * @param array $features
     * @return self
     */
    public function addUser(array $features)
    {
        $this->features[self::USER_FILTERING] = $features;
        return $this;
    }

    /**
     * Filtra os dados do dataset
     *
     * @param string $user
     * @param integer $qtSuggestions
     * @return void
     */
    public function filter($user, $qtSuggestions = 5)
    {
        $scores = $this->calculateUserScores($user, $qtSuggestions);
        $recommendations = array();
        foreach ($scores as $score) {
            $otherUser = key($score);
            $reviewed = $this->features[$otherUser];

            foreach ($reviewed as $feature => $similarity) {
                if (self::CLASSE == $feature) {
                    continue;
                }

                $weight = $similarity * $reviewed[$feature];

                if (isset($recommendations[$feature])) {
                    if ($weight > reset($recommendations[$feature])) {
                        $recommendations[$feature] = [
                            $otherUser => $weight
                        ];
                    }
                    continue;
                }

                $recommendations[$feature] = array(
                    $otherUser => $weight
                );
            }
        }

        return $this->getRecommendations($recommendations);
    }
}

$finder = new WeddingFinder('../data/features.csv');
$classification = $finder
                    ->addUser(
                        [
                            'Escolaridade' => Features::HIGHSCHOOL,
                            'Etinia'       => Features::WHITE,
                            'Regiao'       => Features::NEW_ENGLAND,
                            'Renda'        => Features::POOR,
                            'Filho'        => Features::NOKIDS,
                        ]
                    )
                    ->filter(WeddingFinder::USER_FILTERING, 5);

$similarity = $finder->getFeatureSimilarity(WeddingFinder::USER_FILTERING, $classification['user']);

$similarity = number_format($similarity, 2) * 100;
echo "Usuário - {$classification['user']}" . PHP_EOL;
echo "<br>";
echo "Similaridade - {$similarity}%" . PHP_EOL;
echo "<br>";
echo "Classe - {$classification['class']}";
var_dump($classification['features']);
