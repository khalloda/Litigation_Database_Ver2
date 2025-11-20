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
            return <span className="text-muted">NULL</span>;
        }

        if (isBoolean) {
            return (
                <span className={`badge ${value ? 'bg-success' : 'bg-secondary'}`}>
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
                        <small className="text-muted d-block">
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
                        className="btn btn-sm btn-outline-secondary mb-2"
                        onClick={() => setExpandedJson({ ...expandedJson, [index]: !isExpanded })}
                    >
                        Toggle JSON
                    </button>
                    {isExpanded && (
                        <pre className="mb-0 bg-light p-2 rounded" style={{ maxHeight: '300px', overflowY: 'auto' }}>
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
                        className="btn btn-sm btn-link p-0 ms-1"
                        onClick={() => setExpandedLong({ ...expandedLong, [index]: !isExpanded })}
                    >
                        {isExpanded ? 'Show less' : 'Show more'}
                    </button>
                </div>
            );
        }

        if (isPath && value) {
            return (
                <div className="d-flex align-items-center gap-2">
                    <span>{value}</span>
                    <a href={value} target="_blank" rel="noopener noreferrer" className="btn btn-sm btn-outline-primary">
                        Open
                    </a>
                </div>
            );
        }

        if (isFk && typeof value === 'number') {
            // Try to resolve FK relation (simplified - would need relation data)
            return (
                <div>
                    <span className="text-muted">{value}</span>
                    <span className="ms-2">→</span>
                    <span className="ms-2">(FK)</span>
                </div>
            );
        }

        if (value === '') {
            return <span className="text-muted">(empty string)</span>;
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
            <div className="card mb-4">
                <div className="card-header d-flex justify-content-between align-items-center">
                    <h5 className="mb-0">{title}</h5>
                    <div className="dropdown">
                        <button
                            className="btn btn-sm btn-outline-secondary dropdown-toggle"
                            type="button"
                            id="devToolsDropdown"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            Developer Tools
                        </button>
                        <ul className="dropdown-menu dropdown-menu-end" aria-labelledby="devToolsDropdown">
                            <li>
                                <a className="dropdown-item" href="#" onClick={(e) => { e.preventDefault(); showRawJson(); }}>
                                    View Raw JSON
                                </a>
                            </li>
                            <li>
                                <a className="dropdown-item" href="#" onClick={(e) => { e.preventDefault(); copyCsv(); }}>
                                    Copy CSV
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div className="card-body p-0">
                    <div className="table-responsive">
                        <table className="table table-striped table-hover table-sm align-middle mb-0">
                            <thead className="table-light">
                                <tr>
                                    <th style={{ width: '25%' }}>Field</th>
                                    <th style={{ width: '15%' }}>Type</th>
                                    <th style={{ width: '60%' }}>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                {schema.columns.map((column, index) => {
                                    const columnType = schema.types[column] || 'unknown';
                                    const isFk = schema.fkHints[column] || false;
                                    const value = record[column] ?? null;

                                    return (
                                        <tr key={column}>
                                            <td><code>{column}</code></td>
                                            <td>
                                                <small className="text-muted">{columnType}</small>
                                                {isFk && (
                                                    <span className="badge bg-info ms-1" title="Foreign Key">FK</span>
                                                )}
                                            </td>
                                            <td dir="auto">{formatValue(column, value, index)}</td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Raw JSON Modal */}
            {showRawJsonModal && (
                <div className="modal show d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-lg">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">Raw JSON</h5>
                                <button
                                    type="button"
                                    className="btn-close"
                                    onClick={() => setShowRawJsonModal(false)}
                                    aria-label="Close"
                                ></button>
                            </div>
                            <div className="modal-body">
                                <pre className="bg-light p-3 rounded" style={{ maxHeight: '500px', overflowY: 'auto' }}>
                                    <code>{JSON.stringify(record, null, 2)}</code>
                                </pre>
                            </div>
                            <div className="modal-footer">
                                <button type="button" className="btn btn-secondary" onClick={() => setShowRawJsonModal(false)}>
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
};

export default AllFieldsTable;

