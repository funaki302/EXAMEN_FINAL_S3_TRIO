<?php
// lib_validation.php

function post_trim($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

function Verifier($numero) {
    $regex = '/^(\+2613[23478]\d{7}|03[23478]\d{7})$/';
    return preg_match($regex,$numero);
}

function normalize_telephone($tel) {
    return preg_replace('/\s+/', '', $tel);
}

function TelUnique($numero,$pdo){
    $stmt = $pdo->prepare("SELECT id_user FROM user WHERE phone = ? LIMIT 1");
    $stmt->execute([$numero]);
    if ($stmt->fetch()) {
        return false;
    }
    return true;
}

function PwdUnique($password,$pdo){
    $stmt = $pdo->prepare("SELECT pwd FROM user");
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        if (password_verify($password, $row['pwd'])) {
            return false;
        }
    }
    return true;
}

function EmailUnique($email,$pdo){
    $stmt = $pdo->prepare("SELECT id_user FROM user WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false;
    }
    return true;
}

function validate_registration_input($input, $pdo = null) {
    $errors = [
        'name' => '',
        'email' => '',
        'password' => '',
        'telephone' => '',
    ];

    $values = [
        'name' => trim($input['name'] ?? ''),
        'email' => trim($input['email'] ?? ''),
        'telephone' => normalize_telephone(trim($input['telephone'] ?? '')),
    ];

    $password = $input['password'] ?? '';

    if (mb_strlen($values['name']) < 2) {
        $errors['name'] = "Le nom doit contenir au moins 2 caractères.";
    }

    if ($values['email'] === '') {
        $errors['email'] = "L'email est obligatoire.";
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "L'email n'est pas valide (ex: name@example.com).";
    }

    if (strlen($password) < 8) {
        $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    // Téléphone : 10–13 chiffres, uniquement chiffres
    if (strlen($values['telephone']) < 10 || strlen($values['telephone']) > 13) {
        $errors['telephone'] = "Le téléphone doit contenir entre 10 et 13 chiffres.";
    } elseif (!Verifier($values['telephone'])) {
        $errors['telephone'] = "Le téléphone doit sous ces formes (+261345902348 / 0345791325)";
    }

    $ok = true;
    foreach ($errors as $msg) {
        if ($msg !== '') { $ok = false; break; }
    }

    return ['ok' => $ok, 'errors' => $errors, 'values' => $values];
}

function inscription($input, $pdo){
    $values = [
        'name' => trim($input['name'] ?? ''),
        'email' => trim($input['email'] ?? ''),
        'password' => password_hash(trim($input['password'] ?? ''), PASSWORD_DEFAULT),
        'telephone' => normalize_telephone(trim($input['telephone'] ?? '')),
    ];
    
    $stmt = $pdo->prepare("INSERT INTO user (name, email, pwd, phone, join_date, last_active, role, status, department) VALUES (?, ?, ?, ?, NOW(), 'just now', 'user', 'active', 'General')");
    return $stmt->execute([$values['name'], $values['email'], $values['password'], $values['telephone']]);
}

function login($input, $pdo){
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');
    
    $stmt = $pdo->prepare("SELECT id_user, name, email, pwd, phone FROM user WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['pwd'])) {
        return [
            'id' => $user['id_user'],
            'name' => $user['name'],
            'email' => $user['email'],
            'telephone' => $user['phone']
        ];
    }
    return false;
}

function userExistsByEmail($email, $pdo){
    $stmt = $pdo->prepare("SELECT id_user FROM user WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}