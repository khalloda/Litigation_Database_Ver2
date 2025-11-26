/**
 * Simple in-memory cache for API requests
 */
class RequestCache {
  private cache: Map<string, { data: any; timestamp: number }> = new Map();
  private defaultTTL: number = 5 * 60 * 1000; // 5 minutes default

  /**
   * Get cached data if it exists and is not expired
   */
  get<T = any>(key: string): T | null {
    const cached = this.cache.get(key);
    
    if (!cached) {
      return null;
    }

    const now = Date.now();
    const isExpired = now - cached.timestamp > this.defaultTTL;

    if (isExpired) {
      this.cache.delete(key);
      return null;
    }

    return cached.data as T;
  }

  /**
   * Set data in cache
   */
  set(key: string, data: any, ttl?: number): void {
    this.cache.set(key, {
      data,
      timestamp: Date.now(),
    });

    // Auto-expire after TTL
    if (ttl || this.defaultTTL) {
      setTimeout(() => {
        this.cache.delete(key);
      }, ttl || this.defaultTTL);
    }
  }

  /**
   * Clear specific cache entry
   */
  clear(key: string): void {
    this.cache.delete(key);
  }

  /**
   * Clear all cache entries
   */
  clearAll(): void {
    this.cache.clear();
  }

  /**
   * Clear cache entries matching a pattern
   */
  clearPattern(pattern: string | RegExp): void {
    const regex = typeof pattern === 'string' ? new RegExp(pattern) : pattern;
    
    for (const key of this.cache.keys()) {
      if (regex.test(key)) {
        this.cache.delete(key);
      }
    }
  }

  /**
   * Get cache size
   */
  size(): number {
    return this.cache.size;
  }
}

// Export singleton instance
export const requestCache = new RequestCache();

/**
 * Generate cache key from URL and params
 */
export function generateCacheKey(url: string, params?: any): string {
  if (!params) {
    return url;
  }
  
  const sortedParams = Object.keys(params)
    .sort()
    .map(key => `${key}=${JSON.stringify(params[key])}`)
    .join('&');
  
  return `${url}?${sortedParams}`;
}

/**
 * Wrapper function to cache API requests
 */
export async function cachedRequest<T = any>(
  key: string,
  requestFn: () => Promise<T>,
  ttl?: number
): Promise<T> {
  // Check cache first
  const cached = requestCache.get<T>(key);
  if (cached !== null) {
    return cached;
  }

  // Make request and cache result
  const data = await requestFn();
  requestCache.set(key, data, ttl);
  
  return data;
}
