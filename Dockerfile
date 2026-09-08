FROM php:8.3-apache
# MySQL接続用の拡張モジュールインストールと、CakePHP用のmod_rewrite有効化
RUN docker-php-ext-install mysqli pdo_mysql && a2enmod rewrite
# wp-content/uploads を www-data が書き込めるよう保証（volume mount 後に上書きされるので entrypoint で設定）
RUN echo '#!/bin/bash\nmkdir -p /var/www/html/wp-content/uploads\nchown -R www-data:www-data /var/www/html/wp-content/uploads\nexec apache2-foreground "$@"' > /usr/local/bin/docker-entrypoint-kric.sh \
 && chmod +x /usr/local/bin/docker-entrypoint-kric.sh
ENTRYPOINT ["/usr/local/bin/docker-entrypoint-kric.sh"]