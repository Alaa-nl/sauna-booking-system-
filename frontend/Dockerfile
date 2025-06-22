FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package.json and package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm ci --quiet

# Copy the rest of the code
COPY . .

# Expose the port Vite uses
EXPOSE 5173

# Use non-root user for security
USER node

# Start the Vite dev server
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]