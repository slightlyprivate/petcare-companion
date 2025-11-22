# Build PHP app first
FROM ghcr.io/slightlyprivate/petcare-companion-app:develop AS app

# Production web server
FROM nginx:1.26-alpine3.20

RUN apk update && apk upgrade libxml2
RUN apk add --no-cache curl

# Remove default configs
RUN rm /etc/nginx/conf.d/default.conf

# Copy Laravel app from the build container
COPY --from=app /var/www/html /var/www/html

# Add production nginx config
COPY docker/web/nginx.prod.conf /etc/nginx/conf.d/default.conf

# Logging
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
 CMD curl -f http://localhost/health || exit 1

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
