# Build stage
ARG VITE_APP_VERSION=dev
FROM node:20-alpine AS build
ARG VITE_APP_VERSION
ENV VITE_APP_VERSION=${VITE_APP_VERSION}

RUN apk update && apk upgrade --no-cache --available

WORKDIR /app
COPY src/ui/package*.json ./
RUN --mount=type=cache,target=/root/.npm npm ci
COPY src/ui ./
RUN npm run build

# Runtime stage
FROM nginx:stable-alpine
ARG VITE_APP_VERSION
ENV VITE_APP_VERSION=${VITE_APP_VERSION}

RUN apk update && apk upgrade --no-cache --available

# Copy built UI artifacts
COPY --from=build --chown=nginx:nginx /app/dist /usr/share/nginx/html

# Copy nginx template directory
COPY docker/ui/templates /etc/nginx/templates

# Copy shared security config
COPY docker/shared/nginx/security.conf /etc/nginx/conf.d/

# Remove default nginx config to avoid conflicts with our template
RUN rm -f /etc/nginx/conf.d/default.conf
# Expose port 80 for UI
EXPOSE 80

# Use nginx entrypoint (already supports template variable substitution)
CMD ["nginx", "-g", "daemon off;"]
