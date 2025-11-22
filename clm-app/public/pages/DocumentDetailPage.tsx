import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import type { ClientDocument, DocumentMovementStatus, DocumentMovement } from '../types';
import { useI18n } from '../hooks/useI18n';
import { fetchDocument, fetchDocumentSchema } from '../services/documents';
import MovementForm from '../components/MovementForm';
import { DocumentIcon } from '../components/icons';
import AllFieldsTable from '../components/AllFieldsTable';

const DetailItem: React.FC<{ label: string; value?: React.ReactNode; fullWidth?: boolean }> = ({ label, value, fullWidth }) => {
    if (!value && value !== 0 && value !== false) {
        value = '-';
    }
    return (
        <div className={`py-3 sm:grid sm:grid-cols-3 sm:gap-4 px-1 border-b border-gray-100 ${fullWidth ? 'sm:col-span-2' : ''}`}>
            <dt className="text-sm font-semibold text-gray-500">{label}</dt>
            <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{value}</dd>
        </div>
    );
}

const MovementStatusBadge: React.FC<{ status: DocumentMovementStatus }> = ({ status }) => {
    const { t } = useI18n();
    const statusClasses = {
        checked_out: 'bg-yellow-100 text-yellow-800',
        checked_in: 'bg-blue-100 text-blue-800',
        archived: 'bg-green-100 text-green-800',
        transferred: 'bg-purple-100 text-purple-800',
    };
    return (
        <span className={`px-2 py-1 text-xs font-medium rounded-full ${statusClasses[status]}`}>
            {t(`movement_status.${status}`)}
        </span>
    );
};

const DocumentDetailPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const [activeTab, setActiveTab] = useState<'details' | 'all-fields'>('details');
    const [document, setDocument] = useState<ClientDocument | null>(null);
    const [rawDocument, setRawDocument] = useState<Record<string, any> | null>(null);
    const [schemaData, setSchemaData] = useState<any>(null);
    const [schemaLoading, setSchemaLoading] = useState(true);
    const [schemaError, setSchemaError] = useState<string | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [movementFormState, setMovementFormState] = useState<{ isOpen: boolean; movement: DocumentMovement | null | undefined }>({ isOpen: false, movement: undefined });

    useEffect(() => {
        if (!id) {
            return;
        }

        let isMounted = true;
        setLoading(true);
        setSchemaLoading(true);
        setError(null);
        setSchemaError(null);
        setSchemaData(null);

        const loadData = async () => {
            try {
                const response = await fetchDocument(id);
                const docPayload = response?.data ?? response;
                const rawPayload = response?.raw ?? docPayload;

                if (isMounted) {
                    setDocument(docPayload);
                    setRawDocument(rawPayload);
                }

                const inlineSchema = response?.schema ?? null;
                if (inlineSchema && isMounted) {
                    setSchemaData(inlineSchema);
                    setSchemaLoading(false);
                } else {
                    try {
                        const schemaResponse = await fetchDocumentSchema(id);
                        const resolvedSchema = schemaResponse?.schema ?? schemaResponse;
                        if (isMounted) {
                            setSchemaData(resolvedSchema);
                        }
                    } catch (schemaErr: any) {
                        if (isMounted) {
                            console.error('Error loading document schema:', schemaErr);
                            setSchemaError(schemaErr?.message || 'Failed to load schema metadata.');
                        }
                    } finally {
                        if (isMounted) {
                            setSchemaLoading(false);
                        }
                    }
                }
            } catch (err: any) {
                if (isMounted) {
                    console.error('Error loading document:', err);
                    setError(err?.message || 'Failed to load document');
                }
            } finally {
                if (isMounted) {
                    setLoading(false);
                }
            }
        };

        loadData();

        return () => {
            isMounted = false;
        };
    }, [id]);

    if (loading) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-gray-600">Loading...</p>
                </div>
            </div>
        );
    }

    if (error || !document) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">Error: {error || 'Document not found'}</p>
                    <button onClick={() => navigate('/documents')} className="mt-4 text-primary-600 hover:underline">
                        &larr; {t('app.back_to_documents')}
                    </button>
                </div>
            </div>
        );
    }
    
    const clientName = document.client ? (language === 'ar' ? (document.client.client_name_ar || document.client.client_name_en) : (document.client.client_name_en || document.client.client_name_ar)) : 'N/A';
    const caseName = document.case ? (language === 'ar' ? document.case.case_name_ar : document.case.case_name_en) : 'N/A';
    const recordForAllFields = rawDocument || document;
    
    const handleSaveMovement = (data: any) => {
        console.log("Saving movement:", data);
        // Here you would typically update your state management store or call an API
        setMovementFormState({ isOpen: false, movement: undefined });
    };

    return (
        <div className="container mx-auto">
            <button onClick={() => navigate('/documents')} className="text-primary-600 hover:underline mb-4">&larr; {t('app.back_to_documents')}</button>
            <div className="bg-white rounded-xl shadow-md p-6">
                 <div className="flex justify-between items-start">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-800">{document.document_name}</h1>
                        <p className="text-gray-500 mt-1">{t('document_page.title')}</p>
                    </div>
                    <button 
                        onClick={() => navigate(`/documents/${document.id}/edit`)}
                        className="px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors flex-shrink-0"
                    >
                        {t('document_page.edit_document')}
                    </button>
                 </div>
                
                <div className="border-b border-gray-200 mt-6 mb-6">
                    <div className="flex items-center gap-4">
                        <button
                            onClick={() => setActiveTab('details')}
                            className={`flex items-center gap-2 px-4 py-2 font-semibold rounded-md transition-colors text-sm ${
                                activeTab === 'details' ? 'bg-primary-600 text-white shadow' : 'text-gray-600 hover:bg-primary-100'
                            }`}
                        >
                            {t('document_page.details')}
                        </button>
                        <button
                            onClick={() => setActiveTab('all-fields')}
                            className={`flex items-center gap-2 px-4 py-2 font-semibold rounded-md transition-colors text-sm ${
                                activeTab === 'all-fields' ? 'bg-primary-600 text-white shadow' : 'text-gray-600 hover:bg-primary-100'
                            }`}
                        >
                            <DocumentIcon className="w-4 h-4" />
                            {t('document_page.all_fields') || 'All Fields'}
                        </button>
                    </div>
                </div>

                {activeTab === 'details' && (
                    <div className="mt-6 border-t border-gray-200">
                        <dl>
                            <DetailItem 
                                label={t('documents_page.client')}
                                value={document.client ? <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/clients/${document.client!.id}`); }} className="text-blue-600 hover:underline font-semibold">{clientName}</a> : clientName}
                            />
                            <DetailItem 
                                label={t('documents_page.case')}
                                value={document.case ? <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/cases/${document.case!.id}`); }} className="text-blue-600 hover:underline font-semibold">{caseName}</a> : caseName}
                            />
                            <DetailItem label={t('case.case_number')} value={document.case_number} />
                            <DetailItem label={t('documents_page.type')} value={document.document_type} />
                            <DetailItem label={t('documents_page.deposit_date')} value={document.deposit_date ? new Date(document.deposit_date).toLocaleDateString() : undefined} />
                            <DetailItem label={t('new_document_form.document_date')} value={document.document_date ? new Date(document.document_date).toLocaleDateString() : undefined} />
                            <DetailItem label={t('documents_page.lawyer')} value={document.responsible_lawyer} />
                            <DetailItem label={t('documents_page.storage')} value={<span className="capitalize">{document.document_storage_type}</span>} />
                            <DetailItem label={t('new_document_form.pages_count')} value={document.pages_count} />
                            <DetailItem label={t('new_document_form.uploaded_to_mfiles')} value={document.mfiles_uploaded ? t('case.yes') : t('case.no')} />
                            <DetailItem label={t('new_document_form.mfiles_id')} value={document.mfiles_id} />

                            <DetailItem label={t('common.description')} value={<p className="whitespace-pre-wrap">{document.document_description}</p>} fullWidth />
                            <DetailItem label={t('new_document_form.notes')} value={<p className="whitespace-pre-wrap">{document.notes}</p>} fullWidth />
                        </dl>
                    </div>
                )}

                {activeTab === 'all-fields' && (
                    <div className="mt-6">
                        {schemaLoading && (
                            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 text-blue-800 mb-4">
                                {t('document_page.loading_schema') || 'Loading schema metadata...'}
                            </div>
                        )}

                        {schemaError && (
                            <div className="bg-red-50 border border-red-200 rounded-lg p-4 text-red-800 mb-4">
                                {schemaError}
                            </div>
                        )}

                        {!schemaLoading && !schemaError && !schemaData && (
                            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800">
                                {t('document_page.schema_not_available') || 'Schema data not available.'}
                            </div>
                        )}

                        {schemaData && recordForAllFields && !schemaLoading && !schemaError && (
                            <AllFieldsTable
                                record={recordForAllFields as any}
                                schema={schemaData}
                                title="All Document Fields"
                            />
                        )}
                    </div>
                )}

                {(document.document_storage_type === 'physical' || document.document_storage_type === 'both') && (
                    <div className="mt-6 border-t pt-6">
                        <div className="flex justify-between items-center mb-4">
                            <h2 className="text-xl font-bold text-gray-800">{t('document_page.movement_card_history')}</h2>
                            <button
                                onClick={() => setMovementFormState({ isOpen: true, movement: null })}
                                className="px-4 py-2 bg-green-600 border border-transparent rounded-lg text-white font-semibold hover:bg-green-700 transition-colors text-sm"
                            >
                                {t('document_page.new_move')}
                            </button>
                        </div>
                        {document.movements && document.movements.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full bg-white">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.date')}</th>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.from')}</th>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.to')}</th>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.status')}</th>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.responsible_lawyer')}</th>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm">{t('document_page.movement.notes')}</th>
                                            <th className="text-start p-3 font-semibold text-gray-600 text-sm"></th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {document.movements.map(movement => {
                                            const lawyerName = movement.lawyer ? (language === 'ar' ? movement.lawyer.lawyer_name_ar : movement.lawyer.lawyer_name_en) : 'N/A';
                                            return (
                                                <tr key={movement.id}>
                                                    <td className="p-3 text-sm text-gray-700 whitespace-nowrap">{new Date(movement.date).toLocaleDateString()}</td>
                                                    <td className="p-3 text-sm text-gray-700">{movement.from_location}</td>
                                                    <td className="p-3 text-sm text-gray-700">{movement.to_location}</td>
                                                    <td className="p-3 text-sm"><MovementStatusBadge status={movement.status} /></td>
                                                    <td className="p-3 text-sm text-gray-700 whitespace-nowrap">
                                                        {movement.lawyer ? (
                                                            <a href="#" onClick={(e) => { e.preventDefault(); navigate(`/lawyers/${movement.lawyer!.id}`); }} className="text-blue-600 hover:underline">{lawyerName}</a>
                                                        ) : (
                                                            lawyerName
                                                        )}
                                                    </td>
                                                    <td className="p-3 text-sm text-gray-500">{movement.notes}</td>
                                                    <td className="p-3 text-sm text-center">
                                                        <button 
                                                            onClick={() => setMovementFormState({ isOpen: true, movement })}
                                                            className="text-blue-600 hover:underline font-medium"
                                                        >
                                                            {t('document_page.movement.edit_move')}
                                                        </button>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="text-center text-gray-500 p-6 bg-gray-50 rounded-lg">
                                {t('document_page.movement.no_movements')}
                            </div>
                        )}
                    </div>
                )}
            </div>
            {movementFormState.isOpen && (
                <MovementForm
                    onClose={() => setMovementFormState({ isOpen: false, movement: undefined })}
                    onSave={handleSaveMovement}
                    initialData={movementFormState.movement}
                />
            )}
        </div>
    );
};

export default DocumentDetailPage;