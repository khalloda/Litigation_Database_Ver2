import React, { useState } from 'react';

interface SchemaData {
    columns: string[];
    types: Record<string, string>;
    fkHints: Record<string, boolean>;
}

interface AllFieldsTableProps {
    record: Record<string, any>;
    schema: SchemaData;
    title?: string;
}

const AllFieldsTable: React.FC<AllFieldsTableProps> = ({ record, schema, title = 'All Fields' }) => {
    const [expandedJson, setExpandedJson] = useState<Record<number, boolean>>({});
    const [expandedLong, setExpandedLong] = useState<Record<number, boolean>>({});
    const [showRawJsonModal, setShowRawJsonModal] = useState(false);

    const formatValue = (columnName: string, value: any, index: number): React.ReactNode => {
        const columnType = schema.types[columnName]?.toLowerCase() || 'unknown';
        const isFk = schema.fkHints[columnName] || false;
        const isNull = value === null;
        const isBoolean = ['boolean', 'tinyint(1)', 'bit'].includes(columnType);
        const isDate = ['date', 'datetime', 'timestamp'].includes(columnType);
        const isJson = ['json', 'jsonb'].includes(columnType) || 
                      Array.isArray(value) || 
                      (typeof value === 'object' && value !== null && !(value instanceof Date));
        const isLongString = typeof value === 'string' && value.length > 200;
        const isPath = columnName.endsWith('_path') || columnName.endsWith('_url');

        if (isNull) {
            return <span className="text-gray-400">NULL</span>;
        }

        if (isBoolean) {
            return (
                <span className={`px-2 py-1 text-xs font-medium rounded-full ${value ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                    {value ? 'Yes' : 'No'}
                </span>
            );
        }

        if (isDate) {
            const dateValue = value instanceof Date ? value : new Date(value);
            const isValidDate = !isNaN(dateValue.getTime());
            return (
                <div>
                    <span>{isValidDate ? dateValue.toISOString().split('T')[0] : String(value)}</span>
                    {isValidDate && (
                        <small className="text-gray-500 block">
                            {formatRelativeTime(dateValue)}
                        </small>
                    )}
                </div>
            );
        }

        if (isJson) {
            const jsonStr = JSON.stringify(value, null, 2);
            const isExpanded = expandedJson[index] || false;
            return (
                <div>
                    <button
                        className="px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 mb-2"
                        onClick={() => setExpandedJson({ ...expandedJson, [index]: !isExpanded })}
                    >
                        {isExpanded ? 'Hide JSON' : 'Show JSON'}
                    </button>
                    {isExpanded && (
                        <pre className="bg-gray-50 p-2 rounded text-xs" style={{ maxHeight: '300px', overflowY: 'auto' }}>
                            <code>{jsonStr}</code>
                        </pre>
                    )}
                </div>
            );
        }

        if (isLongString) {
            const isExpanded = expandedLong[index] || false;
            return (
                <div>
                    <span>{isExpanded ? value : `${value.substring(0, 200)}...`}</span>
                    <button
                        className="ml-2 text-primary-600 hover:text-primary-800 text-sm underline"
                        onClick={() => setExpandedLong({ ...expandedLong, [index]: !isExpanded })}
                    >
                        {isExpanded ? 'Show less' : 'Show more'}
                    </button>
                </div>
            );
        }

        if (isPath && value) {
            return (
                <div className="flex items-center gap-2">
                    <span>{value}</span>
                    <a href={value} target="_blank" rel="noopener noreferrer" className="px-2 py-1 text-xs border border-primary-600 text-primary-600 rounded hover:bg-primary-50">
                        Open
                    </a>
                </div>
            );
        }

        if (isFk && typeof value === 'number') {
            // Try to resolve FK relation (simplified - would need relation data)
            return (
                <div>
                    <span className="text-gray-400">{value}</span>
                    <span className="ml-2">→</span>
                    <span className="ml-2 text-gray-500">(FK)</span>
                </div>
            );
        }

        if (value === '') {
            return <span className="text-gray-400">(empty string)</span>;
        }

        return <span dir="auto">{String(value)}</span>;
    };

    const formatRelativeTime = (date: Date): string => {
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return 'Today';
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays} days ago`;
        if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
        if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
        return `${Math.floor(diffDays / 365)} years ago`;
    };

    const copyCsv = () => {
        let csv = 'Field,Value\n';
        schema.columns.forEach(col => {
            const value = record[col] ?? '';
            const csvValue = value === null ? '' : (typeof value === 'object' ? JSON.stringify(value) : String(value));
            csv += `"${col}","${csvValue.replace(/"/g, '""').replace(/\n/g, ' ')}"\n`;
        });
        
        navigator.clipboard.writeText(csv).then(() => {
            alert('CSV copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy:', err);
            alert('Failed to copy CSV. Check console for details.');
        });
    };

    const showRawJson = () => {
        setShowRawJsonModal(true);
    };

    return (
        <>
            <div className="bg-white rounded-xl shadow-md p-6 mb-6">
                <div className="flex justify-between items-center mb-4">
                    <h2 className="text-xl font-semibold text-gray-800">{title}</h2>
                    <div className="relative">
                        <button
                            className="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            onClick={() => setShowRawJsonModal(!showRawJsonModal)}
                        >
                            Developer Tools ▼
                        </button>
                        {showRawJsonModal && (
                            <div className="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                <ul className="py-1">
                                    <li>
                                        <button
                                            className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left"
                                            onClick={() => {
                                                showRawJson();
                                                setShowRawJsonModal(false);
                                            }}
                                        >
                                            View Raw JSON
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left"
                                            onClick={() => {
                                                copyCsv();
                                                setShowRawJsonModal(false);
                                            }}
                                        >
                                            Copy CSV
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        )}
                    </div>
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full bg-white border-collapse">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="text-start p-3 font-semibold text-gray-600 text-sm w-1/4">Field</th>
                                <th className="text-start p-3 font-semibold text-gray-600 text-sm w-1/6">Type</th>
                                <th className="text-start p-3 font-semibold text-gray-600 text-sm w-auto">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            {schema.columns.map((column, index) => {
                                const columnType = schema.types[column] || 'unknown';
                                const isFk = schema.fkHints[column] || false;
                                const value = record[column] ?? null;

                                return (
                                    <tr key={column} className="border-b border-gray-200 hover:bg-gray-50">
                                        <td className="p-3 text-gray-800 font-mono text-sm break-all">{column}</td>
                                        <td className="p-3 text-gray-600 text-sm break-all">
                                            <span>{columnType}</span>
                                            {isFk && (
                                                <span className="ml-1 px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded" title="Foreign Key">FK</span>
                                            )}
                                        </td>
                                        <td className="p-3 text-gray-800 text-sm" dir="auto">{formatValue(column, value, index)}</td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Raw JSON Modal */}
            {showRawJsonModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onClick={() => setShowRawJsonModal(false)}>
                    <div className="bg-white rounded-lg shadow-xl max-w-4xl w-full m-4 max-h-[90vh] flex flex-col" onClick={(e) => e.stopPropagation()}>
                        <div className="flex justify-between items-center p-4 border-b">
                            <h5 className="text-lg font-semibold text-gray-800">Raw JSON</h5>
                            <button
                                type="button"
                                className="text-gray-400 hover:text-gray-600 text-2xl leading-none"
                                onClick={() => setShowRawJsonModal(false)}
                                aria-label="Close"
                            >
                                ×
                            </button>
                        </div>
                        <div className="p-4 overflow-auto flex-1">
                            <pre className="bg-gray-50 p-3 rounded text-xs" style={{ maxHeight: '500px', overflowY: 'auto' }}>
                                <code>{JSON.stringify(record, null, 2)}</code>
                            </pre>
                        </div>
                        <div className="flex justify-end p-4 border-t">
                            <button type="button" className="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300" onClick={() => setShowRawJsonModal(false)}>
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
};

export default AllFieldsTable;

