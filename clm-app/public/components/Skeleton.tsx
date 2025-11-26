import React from 'react';

interface SkeletonProps {
  className?: string;
  width?: string;
  height?: string;
  variant?: 'text' | 'circular' | 'rectangular';
}

export const Skeleton: React.FC<SkeletonProps> = ({ 
  className = '', 
  width, 
  height, 
  variant = 'text' 
}) => {
  const baseClasses = 'animate-pulse bg-gray-200';
  
  const variantClasses = {
    text: 'rounded',
    circular: 'rounded-full',
    rectangular: 'rounded-md',
  };

  const style: React.CSSProperties = {
    width: width || '100%',
    height: height || (variant === 'text' ? '1rem' : '100%'),
  };

  return (
    <div 
      className={`${baseClasses} ${variantClasses[variant]} ${className}`}
      style={style}
    />
  );
};

export const TableSkeleton: React.FC<{ rows?: number; columns?: number }> = ({ 
  rows = 5, 
  columns = 6 
}) => {
  return (
    <div className="bg-white rounded-lg shadow-md overflow-hidden">
      <div className="overflow-x-auto">
        <table className="min-w-full">
          <thead className="bg-gray-50">
            <tr>
              {Array.from({ length: columns }).map((_, i) => (
                <th key={i} className="p-4">
                  <Skeleton height="1rem" />
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200">
            {Array.from({ length: rows }).map((_, rowIndex) => (
              <tr key={rowIndex}>
                {Array.from({ length: columns }).map((_, colIndex) => (
                  <td key={colIndex} className="p-4">
                    <Skeleton height="0.875rem" />
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export const CardSkeleton: React.FC = () => {
  return (
    <div className="bg-white rounded-lg shadow-md p-6 space-y-4">
      <Skeleton height="1.5rem" width="60%" />
      <Skeleton height="1rem" width="40%" />
      <div className="space-y-2">
        <Skeleton height="0.875rem" />
        <Skeleton height="0.875rem" />
        <Skeleton height="0.875rem" width="80%" />
      </div>
    </div>
  );
};

export const ListSkeleton: React.FC<{ items?: number }> = ({ items = 5 }) => {
  return (
    <div className="space-y-3">
      {Array.from({ length: items }).map((_, i) => (
        <div key={i} className="bg-white rounded-lg shadow-sm p-4 flex items-center space-x-4">
          <Skeleton variant="circular" width="3rem" height="3rem" />
          <div className="flex-1 space-y-2">
            <Skeleton height="1rem" width="40%" />
            <Skeleton height="0.875rem" width="60%" />
          </div>
        </div>
      ))}
    </div>
  );
};
