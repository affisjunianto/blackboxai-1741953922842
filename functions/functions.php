// API Credential Functions
function generateApiCredentials($userId) {
    try {
        $pdo = getConnection();
        
        // Generate random API key and secret
        $apiKey = bin2hex(random_bytes(32));
        $apiSecret = bin2hex(random_bytes(32));
        
        // Store credentials
        $stmt = $pdo->prepare("
            INSERT INTO api_credentials (user_id, api_key, api_secret)
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$userId, $apiKey, $apiSecret])) {
            return [
                'api_key' => $apiKey,
                'api_secret' => $apiSecret
            ];
        }
        return false;
    } catch (Exception $e) {
        error_log("Error generating API credentials: " . $e->getMessage());
        return false;
    }
}

function validateApiCredentials($apiKey, $apiSecret) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT ac.*, u.role 
            FROM api_credentials ac
            JOIN users u ON u.id = ac.user_id
            WHERE ac.api_key = ? 
            AND ac.api_secret = ?
            AND ac.is_active = TRUE
        ");
        
        $stmt->execute([$apiKey, $apiSecret]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error validating API credentials: " . $e->getMessage());
        return false;
    }
}

// API Functions
function validateAPIRequest($agentId, $ipAddress) {
