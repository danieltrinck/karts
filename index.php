<?php
require "./api/consulta.php";

$pilots      = []; //Guarda informações sobre o piloto
$pilots_time = []; //Guarda informações sobre o tempo do piloto mais proximo
$v_media     = []; //Guarda informações para calcular a velocidade média durante toda a corrida
$position    = 1;  //Ordem de chegada
$best_time   = ''; //Pega a melhor volta da corrida
$best_pilot  = ''; //Marca qual o piloto fez a melhor volta

uasort($results, function($a,$b)
{   //Ordenando o array por hora do menor para o maior
    if($a['Hora'] == $b['Hora']) {
        return 0;
    }
    return ($a['Hora'] < $b['Hora']) ? -1 : 1;
});

//Calcula o tempo total da corrida
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
        $result['Position']    = $position;
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
    
    //Guarda o último Id do piloto para calcular a distância do piloto da frente
    $last_key = $result['Id'];

    //Soma as velocidades média de cada volta de cada corredor e calcula a velocidade média durante toda a corrida.
    $v_media[$result['Id']] = [
        'VMedia'   => ($v_media[$result['Id']]['VMedia']??0) + str_replace(',','.',$result['VMedia']),
        'QtdVolta' => ($v_media[$result['Id']]['QtdVolta']??0) + 1,
        'Piloto'   => $result['Piloto']
    ];

    if($best_time == "")
    {
        $best_time = $result['TVolta'];

    }else{

        $t  = explode('.',$result['TVolta']); //Tempo da volta
        $bt = explode('.',$best_time);        //Tempo da melhor volta

        if(strtotime('0:'.$bt[0]) >= strtotime('0:'.$t[0]))
        {   //Pega a melhor volta da corrida
            if($bt[1] > $t[1])
            {   //Verifica o milisegundo, pega se for menor que a melhor volta
                $best_time  = $result['TVolta'];
                $best_pilot = $result['Piloto'];
            }
        }
    }
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
        <table>
            <tr>
                <td style="width:50%"><label><p align=left><strong>Duração da Corrida: <?=$timeRun?></strong></p></label></td>
                <td style="width:50%"><label><p align=right><strong>Melhor Volta Realizada Por: <?=$best_pilot?> com tempo de: <?=$best_time?></strong></p></label></td>
            </tr>
        </table>
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