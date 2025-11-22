# Build stage
FROM node:20-alpine AS build

WORKDIR /app
COPY src/ui/package*.json ./
RUN npm install
COPY src/ui ./
RUN npm run build

# Runtime stage
FROM nginx:stable-alpine

# Copy built UI artifacts
COPY --from=build /app/dist /usr/share/nginx/html

# Copy nginx template directory
COPY src/ui/nginx/templates /etc/nginx/templates

# Expose port 80 for UI
EXPOSE 80

# Use nginx entrypoint (already supports template variable substitution)
CMD ["nginx", "-g", "daemon off;"]
