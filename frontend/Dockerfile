# Production Dockerfile for Next.js with Bun
FROM oven/bun:latest

WORKDIR /app

# Install npm for Next.js compatibility (some Next.js internals still require npm)
RUN apt-get update && apt-get install -y npm && rm -rf /var/lib/apt/lists/*

# Copy package files first for better layer caching
COPY package.json bun.lockb* ./

# Install dependencies using Bun
RUN bun install --frozen-lockfile --production

# Copy the rest of the application code
COPY . .

# Set environment variables to prevent npm registry issues
ENV NPM_CONFIG_REGISTRY=https://registry.npmjs.org/
ENV NEXT_TELEMETRY_DISABLED=1

# Build the Next.js application for production
RUN bun run build

# Expose port 3000
EXPOSE 3000

# Start the production server
CMD ["bun", "start"]
