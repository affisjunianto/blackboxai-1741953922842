        // Process file uploads first
        $uploadedFiles = [];
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = [];
                    $maxSize = 5242880; // 5MB default
                    
                    // Set specific constraints based on upload type
                    switch ($key) {
                        case 'site_logo':
                            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                            break;
                        case 'site_favicon':
                            $allowedTypes = ['image/x-icon', 'image/png'];
                            $maxSize = 1048576; // 1MB for favicon
                            break;
                        case 'login_background':
                            $allowedTypes = ['image/jpeg', 'image/png'];
                            $maxSize = 10485760; // 10MB for background
                            break;
                    }
                    
                    $uploadResult = uploadFile($file, __DIR__ . '/../uploads', $allowedTypes, $maxSize);
                    if ($uploadResult) {
                        $uploadedFiles[$key] = $uploadResult;
                    }
                }
            }
        }
