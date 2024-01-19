<?php
require "./api/consulta.php";
$pilots = [];
$position = 1;
$temp_start = '';
$temp_finish = '';
$duration = '';

uasort($results, function($a,$b)
{   //Ordenando o array por hora do menor para o maior
    if($a['Hora'] == $b['Hora']) {
        return 0;
    }
    return ($a['Hora'] < $b['Hora']) ? -1 : 1;
});

$temp_start = $results[0]['Hora'];
$temp_finish = $results[count($results)-1]['Hora'];
$inicio = DateTime::createFromFormat("H:i:s.u", $temp_start);
$fim = DateTime::createFromFormat("H:i:s.u", $temp_finish);

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

uasort($results, function($a,$b)
{   //Ordenando o array por numero de voltas do maior para o menor
    if($a['NVolta'] == $b['NVolta']) {
        return 0;
    }
    return ($a['NVolta'] > $b['NVolta']) ? -1 : 1;
});


foreach($results as $result)
{   //Percorre o array eliminando os duplicados

    if(empty($temp_start))
        $temp_start = $result['Hora'];

    if(empty($pilots[$result['Id']]))
    {
        $result['position'] = $position;
        $pilots[$result['Id']] = $result;
        $position++;

        if(count($pilots) > 1)
        {
            $before_pilot = $pilots[$last_key];
        }
    }
    $last_key = $result['Id'];
    $temp_finish = $result['Hora'];
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
        <div class="header">
            <div>Posição de Chegada</div>
            <div>Código do Piloto</div>
            <div>Nome do Piloto</div>
            <div>Qtde de Voltas Completadas</div>
        </div>
        <div class="body">
            <?php
            foreach($pilots as $pilot){
                echo "<div>".$pilot['position']."</div>";
                echo "<div>".$pilot['Id']."</div>";
                echo "<div>".$pilot['Piloto']."</div>";
                echo "<div>".$pilot['NVolta']."</div>";
            }
            ?>
        </div>
        <label><p align=left><strong>Duração da Corrida: <?=$duration?></strong></p></label>
    </div>
</body>
</html>