# Multi-stage build for the Frontend (BFF + UI static)

FROM node:20-alpine AS ui-builder
WORKDIR /app
# Copy UI and shared sources
COPY src/ui /app/src/ui
COPY src/shared /app/src/shared
WORKDIR /app/src/ui
RUN npm ci || npm install
# Provide production build env defaults for Vite
ARG VITE_API_BASE=/api
ARG VITE_PROXY_BASE=/
ENV VITE_API_BASE=$VITE_API_BASE
ENV VITE_PROXY_BASE=$VITE_PROXY_BASE
RUN VITE_API_BASE=$VITE_API_BASE VITE_PROXY_BASE=$VITE_PROXY_BASE npm run build

FROM node:20-alpine AS server-deps
WORKDIR /app
COPY src/server/package*.json /app/src/server/
WORKDIR /app/src/server
RUN npm ci --omit=dev || npm install --omit=dev

FROM node:20-alpine AS runtime
ENV NODE_ENV=production
WORKDIR /app

# Copy server code and installed deps
COPY --from=server-deps /app/src/server /app/src/server
COPY src/server /app/src/server

# Copy UI dist and shared files
COPY --from=ui-builder /app/src/ui/dist /app/src/ui/dist
COPY src/shared /app/src/shared

EXPOSE 3000
# Point server to UI dist dir for static serving
ENV FRONTEND_DIR=/app/src/ui/dist
ENV SERVER_PORT=3000

CMD ["node", "/app/src/server/src/index.js"]
