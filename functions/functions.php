/**
 * Get a web setting value by key
 * @param string $key The setting key to retrieve
 * @param string $default Default value if setting is not found
 * @return string The setting value or default if not found
 */
function getWebSetting($key, $default = '') {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT setting_value FROM web_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        error_log("Error getting web setting: " . $e->getMessage());
        return $default;
    }
}

/**
 * Get all web settings for a specific group
 * @param string $group The settings group to retrieve
 * @return array Array of settings for the group
 */
function getWebSettingsByGroup($group) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM web_settings WHERE setting_group = ?");
        $stmt->execute([$group]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {
        error_log("Error getting web settings group: " . $e->getMessage());
        return [];
    }
}

function generateCSRFToken() {
