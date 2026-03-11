<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Responde a requisições de pré-verificação do navegador (CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

$arquivo = 'tarefas.json';

// Garante que o arquivo existe e está no formato JSON correto
if (!file_exists($arquivo)) {
    file_put_contents($arquivo, json_encode([]));
}

$metodo = $_SERVER['REQUEST_METHOD'];
$tarefas = json_decode(file_get_contents($arquivo), true);

switch ($metodo) {
    case 'GET': // READ
        echo json_encode($tarefas);
        break;

    case 'POST': // CREATE
        $corpo = json_decode(file_get_contents('php://input'), true);
        if (isset($corpo['nome'])) {
            $nova = ["id" => time(), "nome" => $corpo['nome']];
            $tarefas[] = $nova;
            file_put_contents($arquivo, json_encode($tarefas));
            http_response_code(201);
            echo json_encode($nova);
        }
        break;

    case 'PUT': // UPDATE
        $dados = json_decode(file_get_contents('php://input'), true);
        foreach ($tarefas as &$t) {
            if ($t['id'] == $dados['id']) {
                $t['nome'] = $dados['nome'];
            }
        }
        file_put_contents($arquivo, json_encode($tarefas));
        echo json_encode(["status" => "Atualizado"]);
        break;

    case 'DELETE': // DELETE
        // Pegamos o ID tanto via URL quanto via corpo para garantir funcionamento
        $id = $_GET['id'] ?? json_decode(file_get_contents('php://input'), true)['id'] ?? null;
        if ($id) {
            $tarefas = array_values(array_filter($tarefas, fn($t) => $t['id'] != $id));
            file_put_contents($arquivo, json_encode($tarefas));
            echo json_encode(["status" => "Removido"]);
        } else {
            http_response_code(400);
            echo json_encode(["erro" => "ID nao fornecido"]);
        }
        break;
}