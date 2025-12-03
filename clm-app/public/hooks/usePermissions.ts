import { useEffect, useState } from 'react';
import { fetchCurrentUser } from '../services/auth';
import type { Permission } from '../types';

let cachedPermissions: Permission[] | null = null;
let inFlightPromise: Promise<Permission[] | null> | null = null;

function extractPermissions(payload: any): Permission[] {
  const user = payload?.user ?? payload;
  const raw = user?.permissions ?? [];

  if (!Array.isArray(raw)) {
    return [];
  }

  return raw
    .map((p: any) => {
      if (!p) return null;
      if (typeof p === 'string') return p as Permission;
      if (typeof p.name === 'string') return p.name as Permission;
      return null;
    })
    .filter((p: Permission | null): p is Permission => !!p);
}

export function usePermissions() {
  const [permissions, setPermissions] = useState<Permission[] | null>(cachedPermissions);
  const [loading, setLoading] = useState<boolean>(!cachedPermissions);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (cachedPermissions) {
      return;
    }

    if (!inFlightPromise) {
      inFlightPromise = fetchCurrentUser()
        .then((data) => {
          const perms = extractPermissions(data);
          cachedPermissions = perms;
          return perms;
        })
        .catch((err: any) => {
          console.error('Failed to load permissions:', err);
          return null;
        });
    }

    inFlightPromise
      .then((perms) => {
        if (perms) {
          setPermissions(perms);
        } else {
          setPermissions([]);
        }
      })
      .catch((err) => {
        console.error('Failed to resolve permissions promise:', err);
        setError(err?.message || 'Failed to load permissions');
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  const can = (permission: Permission): boolean => {
    if (!permissions) {
      // While loading or unavailable, fall back to allowing access
      // to avoid locking users out due to transient errors.
      return true;
    }
    return permissions.includes(permission);
  };

  return {
    permissions,
    loading,
    error,
    can,
  };
}


