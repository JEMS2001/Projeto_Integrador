# Use the official Bun image with latest Node.js
FROM oven/bun:latest

WORKDIR /app

# Copy package files
COPY package.json bun.lockb* ./

# Install dependencies using Bun
RUN bun install

# Copy the rest of the application code
COPY . .

# Expose port 3000
EXPOSE 3000

# Start the development server
CMD ["bun", "run", "dev"]
