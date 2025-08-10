# WhatsApp CRM Proxy System Setup

## Overview
This project now includes two proxy solutions to replace the failing moretus.click endpoints:

1. **Enhanced PHP Proxy** (Recommended) - `api/proxy-enhanced.php`
2. **Node.js API Gateway** (High Performance) - `proxy-server/`

## Option 1: Enhanced PHP Proxy (Active)

### Features
- ✅ Rate limiting (100 requests/hour per IP)
- ✅ Caching system (5-minute cache for GET requests)
- ✅ Comprehensive logging
- ✅ Authentication integration
- ✅ URL validation and security checks

### Setup
The PHP proxy is already configured and active. Required directories have been created:
- `logs/` - For proxy request logs
- `cache/` - For response caching and rate limiting

### Usage
The extension automatically uses `api/proxy-enhanced.php` for all blocked domain requests.

### Monitoring
Check logs at: `logs/proxy.log`

## Option 2: Node.js API Gateway (Alternative)

### Features
- ✅ Circuit breaker pattern
- ✅ In-memory caching with NodeCache
- ✅ Advanced rate limiting
- ✅ Health monitoring (`/health` endpoint)
- ✅ Metrics dashboard (`/metrics` endpoint)
- ✅ Production-ready with PM2 support

### Setup
1. Navigate to proxy server directory:
   ```bash
   cd proxy-server
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start development server:
   ```bash
   npm run dev
   ```

4. Or start production server with PM2:
   ```bash
   npm run pm2:start
   ```

### Endpoints
- **Proxy:** `POST http://localhost:3001/api/proxy`
- **Health:** `GET http://localhost:3001/health`
- **Metrics:** `GET http://localhost:3001/metrics`

### Usage
To use Node.js proxy instead of PHP:
1. Start the Node.js server
2. Update extension settings to use `http://localhost:3001/api/proxy`

## Extension Updates Applied

### Files Modified:
- `downloads/model/images/bootstrap.js` - Updated to use enhanced proxy
- `downloads/model/contentScript.bundle.js` - Updated proxy endpoint
- `downloads/model/manifest.json` - Added localhost permissions

### New Files Created:
- `api/proxy-enhanced.php` - Enhanced PHP proxy service
- `proxy-server/index.js` - Node.js API gateway
- `proxy-server/package.json` - Node.js dependencies
- `proxy-server/ecosystem.config.js` - PM2 configuration

## Troubleshooting

### PHP Proxy Issues
1. Check `logs/proxy.log` for errors
2. Ensure `logs/` and `cache/` directories are writable
3. Verify authentication is working

### Node.js Proxy Issues
1. Check if port 3001 is available
2. Verify Node.js version >= 16.0.0
3. Check console logs for errors

## Security Features

### Rate Limiting
- PHP: 100 requests/hour per IP
- Node.js: 100 requests/15 minutes per IP

### Authentication
- PHP: Integrated with existing session system
- Node.js: API key or session token required

### Logging
- All requests are logged with IP, method, URL, and status
- Failed requests include error details

## Performance Optimization

### Caching
- GET requests are cached for 5 minutes
- Reduces API calls and improves response times

### Circuit Breaker (Node.js only)
- Prevents cascade failures
- Automatically recovers when services are back online

## Monitoring

### PHP Proxy
- Check `logs/proxy.log` for request logs
- Monitor `cache/` directory size

### Node.js Proxy
- Health check: `http://localhost:3001/health`
- Metrics: `http://localhost:3001/metrics`
- PM2 monitoring: `pm2 monit`

## Migration from moretus.click

The following endpoints have been replaced:
- `https://st.moretus.click/api/v1/outgoing` → Local proxy
- `https://wp.moretus.click/reseller/server` → Local proxy
- `https://api.moretus.click/api/v1/emoji-*` → Local proxy

All requests now go through your local proxy system, eliminating 404 errors and providing better control and reliability.
