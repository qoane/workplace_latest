        </main>
        <footer class="admin-footer">
            <div class="footer-inner">
                <div>
                    <small>&copy; <?= date('Y') ?> <?= h(site_setting($pdo, 'site_name', 'Workplace Solutions')) ?>. All rights reserved.</small>
                </div>
                <div class="footer-links">
                    <a href="<?= base_url('/') ?>" target="_blank" rel="noopener">View website</a>
                    <span aria-hidden="true">•</span>
                    <a href="mailto:info@workplacesolutions.co.ls">Support</a>
                </div>
            </div>
        </footer>
    </div>
</div>
</body>
</html>
