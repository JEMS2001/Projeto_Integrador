FROM node:20

# Set working directory
WORKDIR /app

# Copy package.json and yarn.lock (or package-lock.json)
COPY package.json yarn.lock ./
# Or if you use package-lock.json
# COPY package.json package-lock.json ./

# Install dependencies
RUN yarn install
# Or if you use npm
# RUN npm install

# Copy the rest of the application code
COPY . .

# Expose port 3000
EXPOSE 3000

# Start the application
CMD ["yarn", "dev"]
# Or if you use npm
# CMD ["npm", "run", "dev"]
