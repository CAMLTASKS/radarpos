mysql -u root -p'RootPassword123!' heladeria < /var/www/html/heladeria/schema.sql
cd /var/www/html/heladeria
php artisan db:seed
php create_admin.php
