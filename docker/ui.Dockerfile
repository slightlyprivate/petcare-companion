# Multi-stage build: build React UI and serve statically with nginx

FROM node:20-alpine AS ui_builder
WORKDIR /app

# Install UI deps
COPY src/ui/package.json ./src/ui/package.json
RUN --mount=type=cache,target=/root/.npm \
    cd src/ui && npm install

# Copy UI sources and build
COPY src/ui ./src/ui
RUN cd src/ui && npm run build


FROM nginx:stable-alpine AS runner
WORKDIR /usr/share/nginx/html

# Copy built UI
COPY --from=ui_builder /app/src/ui/dist .

# Copy nginx configuration for SPA routing
COPY docker/nginx-spa.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]

