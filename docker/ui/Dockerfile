# Build stage
FROM node:20-alpine AS build

WORKDIR /app
COPY src/ui/package*.json ./
RUN --mount=type=cache,target=/root/.npm npm ci
COPY src/ui ./
RUN npm run build

# Runtime stage
FROM nginx:stable-alpine


# Copy built UI artifacts
COPY --from=build /app/dist /usr/share/nginx/html

# Copy nginx template directory
COPY docker/ui/templates /etc/nginx/templates

# Remove default nginx config to avoid conflicts with our template
RUN rm -f /etc/nginx/conf.d/default.conf
# Expose port 80 for UI
EXPOSE 80

# Use nginx entrypoint (already supports template variable substitution)
CMD ["nginx", "-g", "daemon off;"]
