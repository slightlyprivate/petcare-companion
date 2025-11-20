# docker/nginx.prod.Dockerfile
# =============================================================================
# Production-optimized Nginx image for Laravel application
# =============================================================================

FROM nginx:stable-alpine

# Install curl for healthcheck
RUN apk add --no-cache curl

# Remove default nginx config
RUN rm /etc/nginx/conf.d/default.conf

# Copy production nginx configuration
COPY docker/nginx.prod.conf /etc/nginx/conf.d/default.conf

# Create health endpoint directory
RUN mkdir -p /var/www/html/public && \
    echo "OK" > /var/www/html/public/health && \
    chown -R nginx:nginx /var/www/html

# Configure proper logging
RUN ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log

# Healthcheck endpoint
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

EXPOSE 80

STOPSIGNAL SIGQUIT

CMD ["nginx", "-g", "daemon off;"]
