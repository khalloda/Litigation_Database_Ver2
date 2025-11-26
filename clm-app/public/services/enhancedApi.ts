import api from './api';
import { cachedRequest, generateCacheKey, requestCache } from '../utils/requestCache';
import { retryRequest, RetryConfig } from '../utils/retryRequest';

/**
 * Enhanced API service with caching and retry support
 */
export class EnhancedApiService {
  /**
   * Make a GET request with caching support
   */
  static async get<T = any>(
    url: string,
    params?: any,
    options?: {
      cache?: boolean;
      cacheTTL?: number;
      retry?: RetryConfig;
    }
  ): Promise<T> {
    const { cache = false, cacheTTL, retry } = options || {};

    const requestFn = async () => {
      const response = await api.get(url, { params });
      return response.data;
    };

    // With retry
    const executeRequest = retry
      ? () => retryRequest(requestFn, retry)
      : requestFn;

    // With cache
    if (cache) {
      const cacheKey = generateCacheKey(url, params);
      return cachedRequest(cacheKey, executeRequest, cacheTTL);
    }

    return executeRequest();
  }

  /**
   * Make a POST request with retry support
   */
  static async post<T = any>(
    url: string,
    data?: any,
    options?: {
      retry?: RetryConfig;
      invalidateCache?: string | RegExp;
    }
  ): Promise<T> {
    const { retry, invalidateCache } = options || {};

    const requestFn = async () => {
      const response = await api.post(url, data);
      return response.data;
    };

    const result = retry
      ? await retryRequest(requestFn, retry)
      : await requestFn();

    // Invalidate cache if specified
    if (invalidateCache) {
      requestCache.clearPattern(invalidateCache);
    }

    return result;
  }

  /**
   * Make a PUT request with retry support
   */
  static async put<T = any>(
    url: string,
    data?: any,
    options?: {
      retry?: RetryConfig;
      invalidateCache?: string | RegExp;
    }
  ): Promise<T> {
    const { retry, invalidateCache } = options || {};

    const requestFn = async () => {
      const response = await api.put(url, data);
      return response.data;
    };

    const result = retry
      ? await retryRequest(requestFn, retry)
      : await requestFn();

    // Invalidate cache if specified
    if (invalidateCache) {
      requestCache.clearPattern(invalidateCache);
    }

    return result;
  }

  /**
   * Make a DELETE request with retry support
   */
  static async delete<T = any>(
    url: string,
    options?: {
      retry?: RetryConfig;
      invalidateCache?: string | RegExp;
    }
  ): Promise<T> {
    const { retry, invalidateCache } = options || {};

    const requestFn = async () => {
      const response = await api.delete(url);
      return response.data;
    };

    const result = retry
      ? await retryRequest(requestFn, retry)
      : await requestFn();

    // Invalidate cache if specified
    if (invalidateCache) {
      requestCache.clearPattern(invalidateCache);
    }

    return result;
  }

  /**
   * Clear all cache
   */
  static clearCache(): void {
    requestCache.clearAll();
  }

  /**
   * Clear cache by pattern
   */
  static clearCachePattern(pattern: string | RegExp): void {
    requestCache.clearPattern(pattern);
  }
}
