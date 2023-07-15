# Fetching the latest node image on alpine linux
FROM node:alpine

# Setting up the work directory
WORKDIR /app

COPY package.json ./
COPY package-lock.json ./

# Installing dependencies
RUN npm install

# Starting our application
CMD npm start
