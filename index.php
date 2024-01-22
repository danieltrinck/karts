<?php
require "./api/consulta.php";
$pilots = [];
$pilots_time = [];
$v_media = [];
$position = 1;

uasort($results, function($a,$b)
{   //Ordenando o array por hora do menor para o maior
    if($a['Hora'] == $b['Hora']) {
        return 0;
    }
    return ($a['Hora'] < $b['Hora']) ? -1 : 1;
});

$timeRun = calcHours($results[0]['Hora'], $results[count($results)-1]['Hora']);

uasort($results, function($a,$b)
{   //Ordenando o array por numero de voltas do maior para o menor
    if($a['NVolta'] == $b['NVolta']) {
        return 0;
    }
    return ($a['NVolta'] > $b['NVolta']) ? -1 : 1;
});

foreach($results as $result)
{   //Percorre o array eliminando os duplicados

    if(empty($pilots[$result['Id']]))
    {
        $result['Position'] = $position;
        $pilots[$result['Id']] = $result;
        $position++;

        if(count($pilots) > 1)
        {   //Calcula o tempo para o piloto da frente 
            $before_pilot = $pilots[$last_key];
            $duration     = calcHours($result['Hora'], $before_pilot['Hora']);
            $pilots_time[] = [
                "Pilot_Duration" => $duration,
                "Pilot_Before"   => $before_pilot['Piloto'],
                "Pilot_After"    => $result['Piloto']
            ];
        }
    }
    $last_key = $result['Id'];
    $v_media[$result['Id']] = [
        'VMedia'   => ($v_media[$result['Id']]['VMedia']??0) + str_replace(',','.',$result['VMedia']),
        'QtdVolta' => ($v_media[$result['Id']]['QtdVolta']??0) + 1,
        'Piloto'   => $result['Piloto']
    ];
}

function calcHours($temp_start, $temp_finish)
{
    $inicio   = DateTime::createFromFormat("H:i:s.u", $temp_start);
    $fim      = DateTime::createFromFormat("H:i:s.u", $temp_finish);
    $duration = '';
    // Calcular a diferença de tempo
    $diferenca = $inicio->diff($fim);

    // Obter a diferença em horas minutos segundos e milisegundos
    if ($diferenca->h != 0)
        $duration = $diferenca->h.' horas ';

    if ($diferenca->i != 0)
        $duration .= $diferenca->i.' minutos ';

    if ($diferenca->s != 0)
        $duration .= $diferenca->s.' segundos ';

    if ($diferenca->f != 0)
        $duration .= str_replace(".","",$diferenca->f).' milisegundos';
    
    return $duration;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="content">
        <label><p align=center><strong>Resultado da corrida por ordem de chegada</strong></p></label>
        <table>
        <tr>
            <td>Posição de Chegada</td>
            <td>Código do Piloto</td>
            <td>Nome do Piloto</td>
            <td>Qtde de Voltas Completadas</td>
        </tr>
            <?php
            foreach($pilots as $pilot){
                echo "<tr>";
                echo "<td>".$pilot['Position']."</td>";
                echo "<td>".$pilot['Id']."</td>";
                echo "<td>".$pilot['Piloto']."</td>";
                echo "<td>".$pilot['NVolta']."</td>";
                echo "</tr>";
            }
            ?>
        </table>
        <label><p align=left><strong>Duração da Corrida: <?=$timeRun?></strong></p></label>
        <div>
            <?php
            foreach($pilots_time as $pilots){
                echo "<div>".$pilots['Pilot_After']." chegou ".$pilots['Pilot_Duration']." após ".$pilots['Pilot_Before']."</div>";
            }
            ?>
        </div>

        <label><p align=left><strong>Velocidade Média de Cada Corredor Durante Toda Corrida</strong></p></label>
        <div>
            <table>
                <?php
                foreach($v_media as $media){
                    echo "<tr>";
                    echo "<td>".$media['Piloto']."</td>";
                    echo "<td>".number_format(($media['VMedia']/$media['QtdVolta']),3)."</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>