# docker/web/Dockerfile
FROM nginx:stable-alpine

RUN apk add --no-cache curl

# Remove default configs
RUN rm /etc/nginx/conf.d/default.conf

# Add our production reverse proxy config
COPY docker/web/nginx.prod.conf /etc/nginx/conf.d/default.conf

# Logging to stdout / stderr
RUN ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
