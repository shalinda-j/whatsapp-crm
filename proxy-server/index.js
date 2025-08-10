const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const rateLimit = require('express-rate-limit');
const { createProxyMiddleware } = require('http-proxy-middleware');
const NodeCache = require('node-cache');
const axios = require('axios');

// Initialize Express app
const app = express();
const PORT = process.env.PORT || 3001;

// Initialize cache (5 minutes TTL)
const cache = new NodeCache({ stdTTL: 300 });

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // limit each IP to 100 requests per windowMs
  message: {
    error: 'Too many requests from this IP, please try again later.'
  }
});

// Middleware setup
app.use(cors({
  origin: ['https://web.whatsapp.com', 'http://localhost:3000'],
  credentials: true
}));
app.use(helmet());
app.use(morgan('combined'));
app.use(express.json({ limit: '10mb' }));
app.use(limiter);
app.disable('x-powered-by');

// Circuit breaker implementation
class CircuitBreaker {
  constructor(threshold = 5, timeout = 60000) {
    this.threshold = threshold;
    this.timeout = timeout;
    this.failureCount = 0;
    this.lastFailureTime = null;
    this.state = 'CLOSED'; // CLOSED, OPEN, HALF_OPEN
  }

  async call(fn) {
    if (this.state === 'OPEN') {
      if (Date.now() - this.lastFailureTime > this.timeout) {
        this.state = 'HALF_OPEN';
      } else {
        throw new Error('Circuit breaker is OPEN');
      }
    }

    try {
      const result = await fn();
      this.onSuccess();
      return result;
    } catch (error) {
      this.onFailure();
      throw error;
    }
  }

  onSuccess() {
    this.failureCount = 0;
    this.state = 'CLOSED';
  }

  onFailure() {
    this.failureCount++;
    this.lastFailureTime = Date.now();
    if (this.failureCount >= this.threshold) {
      this.state = 'OPEN';
    }
  }
}

// Create circuit breakers for different services
const circuitBreakers = new Map();

function getCircuitBreaker(service) {
  if (!circuitBreakers.has(service)) {
    circuitBreakers.set(service, new CircuitBreaker());
  }
  return circuitBreakers.get(service);
}

// Authentication middleware
async function authenticate(req, res, next) {
  const apiKey = req.headers['x-api-key'];
  const sessionToken = req.headers['authorization'];

  if (!apiKey && !sessionToken) {
    return res.status(401).json({ error: 'Authentication required' });
  }

  // Add your authentication logic here
  // For now, we'll assume authentication is valid
  req.user = { id: 'user123', authenticated: true };
  next();
}

// Proxy endpoint
app.post('/api/proxy', authenticate, async (req, res) => {
  try {
    const { url, method = 'GET', headers = {}, body, cache: useCache = false } = req.body;

    if (!url) {
      return res.status(400).json({ error: 'URL is required' });
    }

    // Validate URL
    try {
      new URL(url);
    } catch (error) {
      return res.status(400).json({ error: 'Invalid URL format' });
    }

    // Check cache for GET requests
    const cacheKey = `${method}:${url}:${JSON.stringify(headers)}`;
    if (method === 'GET' && useCache) {
      const cachedResponse = cache.get(cacheKey);
      if (cachedResponse) {
        console.log(`Cache hit for ${url}`);
        return res.json(cachedResponse);
      }
    }

    // Get circuit breaker for this service
    const serviceHost = new URL(url).hostname;
    const circuitBreaker = getCircuitBreaker(serviceHost);

    // Make request through circuit breaker
    const result = await circuitBreaker.call(async () => {
      const config = {
        method,
        url,
        headers: {
          ...headers,
          'User-Agent': 'WhatsApp-CRM-Proxy/2.0'
        },
        timeout: 30000,
        validateStatus: () => true // Don't throw on HTTP error status
      };

      if (body && ['POST', 'PUT', 'PATCH'].includes(method.toUpperCase())) {
        config.data = body;
      }

      return await axios(config);
    });

    const response = {
      ok: result.status >= 200 && result.status < 300,
      status: result.status,
      data: result.data,
      headers: result.headers['content-type'] || ''
    };

    // Cache successful GET requests
    if (method === 'GET' && useCache && response.ok) {
      cache.set(cacheKey, response);
    }

    res.status(result.status).json(response);

  } catch (error) {
    console.error('Proxy error:', error.message);
    
    if (error.message === 'Circuit breaker is OPEN') {
      return res.status(503).json({ 
        error: 'Service temporarily unavailable',
        details: 'Circuit breaker is open'
      });
    }

    res.status(500).json({
      error: 'Proxy request failed',
      details: error.message
    });
  }
});

// Health check endpoint
app.get('/health', (req, res) => {
  const circuitBreakerStatus = {};
  for (const [service, cb] of circuitBreakers.entries()) {
    circuitBreakerStatus[service] = {
      state: cb.state,
      failureCount: cb.failureCount
    };
  }

  res.json({
    status: 'healthy',
    timestamp: new Date().toISOString(),
    cache: {
      keys: cache.keys().length,
      stats: cache.getStats()
    },
    circuitBreakers: circuitBreakerStatus
  });
});

// Metrics endpoint
app.get('/metrics', (req, res) => {
  res.json({
    cache: cache.getStats(),
    circuitBreakers: Array.from(circuitBreakers.entries()).map(([service, cb]) => ({
      service,
      state: cb.state,
      failureCount: cb.failureCount,
      lastFailureTime: cb.lastFailureTime
    }))
  });
});

// Start server
app.listen(PORT, () => {
  console.log(`🚀 WhatsApp CRM Proxy Server running on port ${PORT}`);
  console.log(`📊 Health check: http://localhost:${PORT}/health`);
  console.log(`📈 Metrics: http://localhost:${PORT}/metrics`);
});

module.exports = app;
