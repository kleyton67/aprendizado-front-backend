<?php
//php -f index.php -S localhost:8000
//sudo apt install php-sqlite3

// Configurações do banco de dados SQLite
$dbFile = 'database.db';
$conn = new SQLite3($dbFile);

// Criar tabela de usuários, se não existir
$conn->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(45),
        email VARCHAR(60)
    )
");

// Definir rotas
$route = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($route) {
    case '/':
        // Rota raiz
        if ($method === 'GET') {
            echo "Bem-vindo ao backend em PHP com SQLite!";
        } else {
            http_response_code(405);
            echo "Método não permitido";
        }
        break;
    case '/users':
        // Rota de usuários
        if ($method === 'GET') {
            $users = getAllUsers($conn);
            echo json_encode($users);
        } elseif ($method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            createUser($conn, $data);
        } else {
            http_response_code(405);
            echo "Método não permitido";
        }
        break;
    case '/users/:id':
        // Rota de usuário individual
        $id = explode('/', $route)[2];
        if ($method === 'GET') {
            $user = getUser($conn, $id);
            echo json_encode($user);
        } 
        // elseif ($method === 'PUT') {
        //     $data = json_decode(file_get_contents("php://input"), true);
        //     updateUser($conn, $id, $data);
        // } elseif ($method === 'DELETE') {
        //     deleteUser($conn, $id);
        // } else {
        //     http_response_code(405);
        //     echo "Método não permitido";
        // }
        break;
    default:
        // Rota não encontrada
        http_response_code(404);
        echo "Rota não encontrada";
        break;
}

// Função para obter todos os usuários
function getAllUsers($conn) {
    $result = $conn->query("SELECT * FROM users");
    $users = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $users[] = $row;
    }
    return $users;
}

// Função para obter um usuário
function getUser($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}

// Função para criar um novo usuário
function createUser($conn, $data) {
    $name = $data['name'];
    $email = $data['email'];
    $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (:name, :email)");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    if ($stmt->execute()) {
        http_response_code(201);
        echo "Usuário criado com sucesso";
    } else {
        http_response_code(400);
        echo "Erro ao criar usuário: " . $conn->lastErrorMsg();
    }
}

?>