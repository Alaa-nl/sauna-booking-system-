#!/bin/bash

echo "Testing connection to services..."

echo "1. Testing Nginx..."
curl -I http://localhost

echo "2. Testing API endpoint..."
curl -X POST -H "Content-Type: application/json" -d '{"username":"admin","password":"admin123"}' http://localhost/api/users/login

echo "3. Testing Frontend..."
curl -I http://localhost:5173

echo "Connection tests completed."