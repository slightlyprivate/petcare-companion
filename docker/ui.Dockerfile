# Multi-stage build: build React UI, run Express BFF, serve UI statically

FROM node:20-alpine AS ui_builder
WORKDIR /app

# Install UI deps
COPY src/ui/package.json ./src/ui/package.json
RUN --mount=type=cache,target=/root/.npm \
    cd src/ui && npm install

# Copy UI sources and build
COPY src/ui ./src/ui
RUN cd src/ui && npm run build


FROM node:20-alpine AS runner
ENV NODE_ENV=production
WORKDIR /app

# Install server production deps only
COPY src/server/package.json ./src/server/package.json
RUN --mount=type=cache,target=/root/.npm \
    cd src/server && npm install --omit=dev

# Copy server sources
COPY src/server/src ./src/server/src

# Copy built UI
COPY --from=ui_builder /app/src/ui/dist ./ui

# Default environment (can override in compose)
ENV SERVER_PORT=3000 \
    BACKEND_URL=http://web:80 \
    FRONTEND_DIR=/app/ui

EXPOSE 3000
CMD ["node", "./src/server/src/index.js"]

