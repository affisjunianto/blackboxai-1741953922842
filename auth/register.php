        // Create agent (website registration = no parent)
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role) 
            VALUES (?, ?, ?, 'agent')
        ");
        
        if ($stmt->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT)
        ])) {
            setFlashMessage('success', 'Registration successful! Please login.');
            redirect('/auth/login.php');
        } else {
            setFlashMessage('danger', 'Username or email already exists.');
        }
