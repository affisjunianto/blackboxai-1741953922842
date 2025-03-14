    <!-- Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars(getWebSetting('meta_description')); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(getWebSetting('meta_keywords')); ?>">
    
    <title><?php echo htmlspecialchars(getWebSetting('site_name')); ?> - <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Welcome'; ?></title>
    
    <!-- Favicon -->
    <?php if ($favicon = getWebSetting('site_favicon')): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($favicon); ?>">
    <?php endif; ?>
