# docker/ui/Dockerfile
# =====================================================
# Build React UI with Node
# =====================================================
FROM node:20-alpine AS builder

WORKDIR /app

# Copy and install dependencies
COPY src/ui/package.json src/ui/package-lock.json ./ui/
RUN --mount=type=cache,target=/root/.npm \
    cd ui && npm ci --quiet

# Copy source code and build
COPY src/ui ./ui
RUN cd ui && npm run build

# =====================================================
# Serve UI statically with nginx
# =====================================================
FROM nginx:stable-alpine AS runner

# Clean default nginx config
RUN rm /etc/nginx/conf.d/default.conf

# Copy build output
COPY --from=builder /app/ui/dist /usr/share/nginx/html

# Copy optimized SPA nginx config
COPY docker/ui/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
