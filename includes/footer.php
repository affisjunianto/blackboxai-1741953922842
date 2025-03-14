</div> <!-- Close main-content div -->

<!-- Footer -->
<footer class="bg-light py-4 mt-auto">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <?php echo htmlspecialchars(getWebSetting('footer_text', '© ' . date('Y') . ' Ampibet. All rights reserved.')); ?>
            </div>
            <div class="col-md-6">
                <?php
                $footerLinks = getWebSetting('footer_links');
                if ($footerLinks) {
                    echo '<ul class="list-inline text-center text-md-end mb-0">';
                    foreach (explode("\n", $footerLinks) as $link) {
                        $parts = explode('|', $link);
                        if (count($parts) === 2) {
                            echo '<li class="list-inline-item">';
                            echo '<a href="' . htmlspecialchars(trim($parts[1])) . '" class="text-muted text-decoration-none">' . htmlspecialchars(trim($parts[0])) . '</a>';
                            echo '</li>';
                        }
                    }
                    echo '</ul>';
                }
                ?>
            </div>
        </div>
    </div>
</footer>

<!-- Google Analytics -->
<?php if ($gaId = getWebSetting('google_analytics_id')): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($gaId); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?php echo htmlspecialchars($gaId); ?>');
</script>
<?php endif; ?>
