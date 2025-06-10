<?php
session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

header('Content-Type: application/json');
$response = [
    'status' => '',
    'message' => '',
    'data' => []
];

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $response['status'] = 'success';
    $response['message'] = 'User is logged in';
    $response['data'] = [
        'loggedin' => true,
        'user_id' => $_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'],
        'email' => $_SESSION['email'],
        'username' => $_SESSION['username'],
        'profile_picture' => $_SESSION['profile_picture']
    ];
    echo json_encode($response);
    exit;
}

include '../../config.php';
$query = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = strtolower(trim($_POST['username']));
        $password = trim($_POST['password']);

        $result = $query->select('users', '*', "username = ?", [$username], 's');

        if (!empty($result)) {
            $user = $result[0];

            if ($user['password'] == $query->hashPassword($password)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['profile_picture'] = $user['profile_picture'];

                setcookie('username', $username, time() + (86400 * 30), "/", "", true, true);
                setcookie('session_token', session_id(), time() + (86400 * 30), "/", "", true, true);

                $response['status'] = 'success';
                $response['message'] = 'Login successful';
                $response['data'] = [
                    'loggedin' => true,
                    'user_id' => $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'profile_picture' => $user['profile_picture']
                ];
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Incorrect username or password';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'No user found with that username';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Please provide both username and password';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
