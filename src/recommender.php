<?php
/**
 * @copyright 2020
 * @author    Vinicius Alves <vinicius_o.a@live.com>
 * @package   FAST_PROJ
 * @since     2020-11-30
*/

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
     * Dataset
     *
     * @var array
     */
    protected $features = array(
        'User 1' => array(
            'Escolaridade' => 0.5776576491728649,
            'Etinia' => 0.6047959402892891,
            'Regiao' => 0.6055689603699992,
            'Renda' => 0.4250974866145632,
            'Filho' => 0.36471612937220166,
            'classificacao' => 'IMPROVAVEL'
        ),
        'User 2' => array(
            'Escolaridade' => 0.2776576491728649,
            'Etinia' => 0.8047959402892891,
            'Regiao' => 0.1055689603699992,
            'Renda' => 0.6250974866145632,
            'Filho' => 0.96471612937220166,
            'classificacao' => 'PROVAVEL'
        ),
        'User 3' => array(
            'Escolaridade' => 0.8776576491728649,
            'Etinia' => 0.9047959402892891,
            'Regiao' => 0.5055689603699992,
            'Renda' => 0.2250974866145632,
            'Filho' => 0.76471612937220166,
            'classificacao' => 'IMPROVAVEL'
        ),
        'User 4' => array(
            'Escolaridade' => 0.1776576491728649,
            'Etinia' => 0.047959402892891,
            'Regiao' => 0.1055689603699992,
            'Renda' => 0.1250974866145632,
            'Filho' => 0.06471612937220166,
            'classificacao' => 'IMPROVAVEL'
        ),
        'User 5' => array(
            'Escolaridade' => 0.1676576491728649,
            'Etinia' => 0.3447959402892891,
            'Regiao' => 0.9755689603699992,
            'Renda' => 0.03500974866145632,
            'Filho' => 0.04071612937220166,
            'classificacao' => 'IMPROVAVEL'
        ),
        'User 6' => array(
            'Escolaridade' => 0.5776576491728649,
            'Etinia' => 0.7747959402892891,
            'Regiao' => 0.3555689603699992,
            'Renda' => 0.5750974866145632,
            'Filho' => 0.8871612937220166,
            'classificacao' => 'PROVAVEL'
        ),
        'User 7' => array(
            'Escolaridade' => 0.1176576491728649,
            'Etinia' => 0.3347959402892891,
            'Regiao' => 0.9055689603699992,
            'Renda' => 0.00950974866145632,
            'Filho' => 0.78471612937220166,
            'classificacao' => 'IMPROVAVEL'
        ),
        'User 8' => array(
            'Escolaridade' => 0.5676576491728649,
            'Etinia' => 0.7847959402892891,
            'Regiao' => 0.9955689603699992,
            'Renda' => 0.3250974866145632,
            'Filho' => 0.89471612937220166,
            'classificacao' => 'PROVAVEL'
        ),
        'User 9' => array(
            'Escolaridade' => 0.1374576491728649,
            'Etinia' => 0.4047959402892891,
            'Regiao' => 0.9055689603699992,
            'Renda' => 0.8250974866145632,
            'Filho' => 0.46471612937220166,
            'classificacao' => 'IMPROVAVEL'
        ),
        'User 10' => array(
            'Escolaridade' => 0.0776576491728649,
            'Etinia' => 0.0947959402892891,
            'Regiao' => 0.1255689603699992,
            'Renda' => 0.1450974866145632,
            'Filho' => 0.67471612937220166,
            'classificacao' => 'IMPROVAVEL'
        ),
        'User 11' => array(
            'Escolaridade' => 0.5776576491728649,
            'Etinia' => 0.6047959402892891,
            'Regiao' => 0.6055689603699992,
            'Renda' => 0.4250974866145632,
            'Filho' => 0.36471612937220166,
            'classificacao' => 'PROVAVEL'
        )
    );

    /**
     * Retorna o score das features dos usuários
     *
     * @param string $firstUser
     * @param string $secondUser
     * @return array
     */
    protected function getFeatures($firstUser, $secondUser)
    {
        $tuple = array();
        array_map(function($data1, $data2) use (&$tuple) {
            if (is_string($data1) || is_string($data2)) {
                return;
            }
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
        $tuple = array();
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
        $tuple = array();
        foreach (array_keys($this->features) as $otherUser) {
            if ($otherUser == $user) {
                continue;
            }

            array_push(
                $tuple,
                array(
                    $otherUser => $this->getFeatureSimilarity(
                        $user,
                        $otherUser
                    )
                )
            );
        }

        sort($tuple);
        $tuple = array_reverse($tuple);
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
        $recommend = array();
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
        return $this->features[$user][self::CLASSE];
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

                # Calculando o peso e a similaridade entre as avaliações
                $weight = $similarity * $reviewed[$feature];

                if (isset($recommendations[$feature])) {
                    if ($weight > reset($recommendations[$feature])) {
                        $recommendations[$feature] = array(
                            $otherUser => $weight
                        );
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

$finder = new WeddingFinder();
$similarity = $finder->getFeatureSimilarity('User 1', 'User 10');
$classification = $finder->filter('User 1', 1);

$similarity = number_format($similarity, 2) * 100;
echo "Similaridade - {$similarity}%" . PHP_EOL;
echo "<br>";
echo "Classe - {$classification}";
