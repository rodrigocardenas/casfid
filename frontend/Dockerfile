# ============================================
# Node.js 18-Alpine Frontend (Next.js 14)
# ============================================
# Multi-stage build para optimizar imagen

# Stage 1: Dependencies & Build
FROM node:18-alpine AS builder

ARG NODE_VERSION=18-alpine

WORKDIR /app

# ============================================
# System Dependencies
# ============================================
RUN apk add --no-cache \
    build-base \
    python3 \
    git

# ============================================
# Install npm dependencies
# ============================================
COPY frontend/package.json frontend/package-lock.json* ./
RUN npm ci

# ============================================
# Copy source code
# ============================================
COPY frontend/ .

# ============================================
# Build Next.js application
# ============================================
RUN npm run build

# ============================================
# Stage 2: Production Runtime
# ============================================
FROM node:18-alpine

WORKDIR /app

# ============================================
# Install only production dependencies
# ============================================
COPY frontend/package.json frontend/package-lock.json* ./
RUN npm ci --only=production && npm cache clean --force

# ============================================
# Copy built application from builder
# ============================================
COPY --from=builder /app/.next ./.next
COPY --from=builder /app/public ./public
COPY --from=builder /app/node_modules ./node_modules
COPY frontend/next.config.js ./next.config.js
COPY frontend/tsconfig.json ./tsconfig.json
COPY frontend/.env.local* ./

# ============================================
# Environment Variables
# ============================================
ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

# ============================================
# Create non-root user for security
# ============================================
RUN addgroup -g 1001 -S nodejs && \
    adduser -S nextjs -u 1001

# ============================================
# Change ownership of app directory
# ============================================
RUN chown -R nextjs:nodejs /app

# ============================================
# Switch to non-root user
# ============================================
USER nextjs

# ============================================
# Health Check
# ============================================
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD wget --quiet --tries=1 --spider http://localhost:3000/api/health || exit 1

# ============================================
# Expose Port
# ============================================
EXPOSE 3000

# ============================================
# Start Next.js
# ============================================
CMD ["npm", "start"]
