FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx && \
    docker-php-ext-install mysqli

COPY . /var/www/html/

COPY --chmod=755 <<'EOF' /start.sh
#!/bin/sh
php-fpm -D
nginx -g "daemon off;"
EOF

RUN echo 'server { \
    listen 80; \
    root /var/www/html; \
    index index.php; \
    location / { try_files $uri $uri/ /index.php?$query_string; } \
    location ~ \.php$ { fastcgi_pass 127.0.0.1:9000; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; include fastcgi_params; } \
}' > /etc/nginx/http.d/default.conf

EXPOSE 80

CMD ["/start.sh"]
