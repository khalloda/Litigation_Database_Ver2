import { useState, useCallback } from 'react';

/**
 * Hook for handling optimistic UI updates
 * 
 * @param initialData The initial data
 * @param updateFn The async function to update data on the server
 * @param rollbackFn Optional function to run if the update fails
 */
export function useOptimistic<T>(
  initialData: T,
  updateFn: (data: T) => Promise<any>,
  rollbackFn?: (error: any) => void
) {
  const [data, setData] = useState<T>(initialData);
  const [isPending, setIsPending] = useState(false);
  const [error, setError] = useState<any>(null);

  const update = useCallback(async (newData: T) => {
    // Keep reference to previous data for rollback
    const previousData = data;
    
    // Optimistically update state
    setData(newData);
    setIsPending(true);
    setError(null);

    try {
      // Perform actual server update
      await updateFn(newData);
      setIsPending(false);
    } catch (err) {
      // Revert on failure
      setData(previousData);
      setError(err);
      setIsPending(false);
      
      if (rollbackFn) {
        rollbackFn(err);
      }
    }
  }, [data, updateFn, rollbackFn]);

  return { data, update, isPending, error, setData };
}
