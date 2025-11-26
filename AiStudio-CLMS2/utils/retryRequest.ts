import { AxiosError, AxiosRequestConfig } from 'axios';

export interface RetryConfig {
  maxRetries?: number;
  retryDelay?: number;
  retryCondition?: (error: AxiosError) => boolean;
  onRetry?: (retryCount: number, error: AxiosError) => void;
}

/**
 * Default retry condition - retry on network errors and 5xx server errors
 */
const defaultRetryCondition = (error: AxiosError): boolean => {
  // Retry on network errors
  if (!error.response) {
    return true;
  }

  // Retry on 5xx server errors
  const status = error.response.status;
  return status >= 500 && status < 600;
};

/**
 * Calculate exponential backoff delay
 */
function calculateBackoff(retryCount: number, baseDelay: number = 1000): number {
  // Exponential backoff: delay * 2^retryCount with jitter
  const exponentialDelay = baseDelay * Math.pow(2, retryCount);
  const jitter = Math.random() * 1000; // Add random jitter up to 1 second
  
  return Math.min(exponentialDelay + jitter, 30000); // Cap at 30 seconds
}

/**
 * Retry a request with exponential backoff
 */
export async function retryRequest<T = any>(
  requestFn: () => Promise<T>,
  config: RetryConfig = {}
): Promise<T> {
  const {
    maxRetries = 3,
    retryDelay = 1000,
    retryCondition = defaultRetryCondition,
    onRetry,
  } = config;

  let lastError: any;
  
  for (let attempt = 0; attempt <= maxRetries; attempt++) {
    try {
      return await requestFn();
    } catch (error) {
      lastError = error;
      
      // Check if we should retry
      const shouldRetry = 
        attempt < maxRetries && 
        error instanceof Error &&
        retryCondition(error as AxiosError);

      if (!shouldRetry) {
        throw error;
      }

      // Calculate delay with exponential backoff
      const delay = calculateBackoff(attempt, retryDelay);
      
      // Call onRetry callback if provided
      if (onRetry) {
        onRetry(attempt + 1, error as AxiosError);
      }

      // Wait before retrying
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }

  throw lastError;
}

/**
 * Axios request config with retry support
 */
export interface AxiosRetryConfig extends AxiosRequestConfig {
  retry?: RetryConfig;
}
