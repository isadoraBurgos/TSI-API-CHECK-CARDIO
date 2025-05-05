<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $idade = (int)$_POST['age'];
    $sexo = (int)$_POST['sex'];
    $dorPeito = (int)$_POST['cp'];
    $pressao = (int)$_POST['trestbps'];
    $colesterol = (int)$_POST['chol'];
    $glicemia = (int)$_POST['fbs'];
    $eletro = (int)$_POST['restecg'];
    $freqCardiaca = (int)$_POST['thalach'];
    $angina = (int)$_POST['exang'];
    $depressaoST = (float)$_POST['oldpeak'];
    $inclinacaoST = (int)$_POST['slope'];
    $vasos = (int)$_POST['ca'];
    $talassemia = (int)$_POST['thal'];

    $erros = [];
    if (empty($nome)) $erros[] = "Nome não pode estar vazio";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "E-mail inválido";
    if ($idade < 20 || $idade > 100) $erros[] = "Idade deve ser entre 20 e 100 anos";
    if ($sexo != 0 && $sexo != 1) $erros[] = "Sexo inválido";
    if ($dorPeito < 0 || $dorPeito > 3) $erros[] = "Tipo de dor no peito inválido";
    if ($pressao < 90 || $pressao > 200) $erros[] = "Pressão arterial inválida";
    if ($colesterol < 100 || $colesterol > 400) $erros[] = "Colesterol inválido";
    if ($glicemia != 0 && $glicemia != 1) $erros[] = "Glicemia inválida";
    if ($eletro < 0 || $eletro > 2) $erros[] = "Eletrocardiograma inválido";
    if ($freqCardiaca < 60 || $freqCardiaca > 220) $erros[] = "Frequência cardíaca inválida";
    if ($angina != 0 && $angina != 1) $erros[] = "Angina inválida";
    if ($depressaoST < 0 || $depressaoST > 6) $erros[] = "Depressão ST inválida";
    if ($inclinacaoST < 0 || $inclinacaoST > 2) $erros[] = "Inclinação ST inválida";
    if ($vasos < 0 || $vasos > 3) $erros[] = "Número de vasos inválido";
    if ($talassemia < 0 || $talassemia > 3) $erros[] = "Talassemia inválida";

    if (!empty($erros)) {
        echo json_encode(['erro' => implode('; ', $erros)]);
        exit;
    }

    $conexao = mysqli_connect('localhost', 'root', '', 'banco_ficticio');
    if (!$conexao) {
        echo json_encode(['erro' => 'Erro ao conectar ao banco de dados']);
        exit;
    }

    $query = "INSERT INTO avaliacao_cardiaco (nome, email, age, sex, cp, trestbps, chol, fbs, restecg, thalach, exang, oldpeak, slope, ca, thal) 
              VALUES ('$nome', '$email', $idade, $sexo, $dorPeito, $pressao, $colesterol, $glicemia, $eletro, $freqCardiaca, $angina, $depressaoST, $inclinacaoST, $vasos, $talassemia)";
    if (!mysqli_query($conexao, $query)) {
        echo json_encode(['erro' => 'Erro ao salvar os dados']);
        exit;
    }

    mysqli_close($conexao);

    $url = 'http://localhost:8000/?' . http_build_query([
        'idade' => $idade,
        'sexo' => $sexo,
        'dor_peito' => $dorPeito,
        'pressao' => $pressao,
        'colesterol' => $colesterol,
        'glicemia' => $glicemia,
        'eletro' => $eletro,
        'freq_cardiaca' => $freqCardiaca,
        'angina' => $angina,
        'depressao_st' => $depressaoST,
        'inclinacao_st' => $inclinacaoST,
        'vasos' => $vasos,
        'talassemia' => $talassemia
    ]);
    $risco = @file_get_contents($url);
    if ($risco === false) {
        echo json_encode(['erro' => 'Erro ao chamar o script Python']);
        exit;
    }

    $assunto = "Resultado da Avaliação Cardíaca";
    $mensagem = "
        <h2>Resultado da Avaliação Cardíaca</h2>
        <p><b>Atenção:</b> Este resultado é apenas uma simulação. Consulte um médico para um diagnóstico real.</p>
        <p><b>Nome:</b> $nome</p>
        <p><b>Idade:</b> $idade anos</p>
        <p><b>Sexo:</b> " . ($sexo == 1 ? 'Masculino' : 'Feminino') . "</p>
        <p><b>Resultado:</b> $risco</p>
        <p>Procure um médico para mais detalhes.</p>
    ";

    $cabecalhos = "From: naoresponda@checkcardio.com\r\n";
    $cabecalhos .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (mail($email, $assunto, $mensagem, $cabecalhos)) {
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Dados salvos e e-mail enviado!',
            'risco' => $risco
        ]);
    } else {
        echo json_encode(['erro' => 'Erro ao enviar o e-mail']);
    }
} else {
    echo json_encode(['erro' => 'Acesso inválido']);
}
?>