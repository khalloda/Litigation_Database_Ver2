import React, { useState } from 'react';
import { useI18n } from '../hooks/useI18n';
import type { DocumentAnalysisResult } from '../types';
import { analyzeDocument } from '../services/ai';
import { SparklesIcon } from './icons';

const ResultSection: React.FC<{title: string; children: React.ReactNode}> = ({title, children}) => (
    <div className="mt-4">
        <h4 className="text-md font-bold text-gray-800 border-b pb-2 mb-2">{title}</h4>
        {children}
    </div>
);

const DocumentAnalyzer: React.FC = () => {
    const { t, language } = useI18n();
    const [documentText, setDocumentText] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [analysisResult, setAnalysisResult] = useState<DocumentAnalysisResult | null>(null);

    const handleAnalyze = async () => {
        if (!documentText.trim()) return;

        setIsLoading(true);
        setError(null);
        setAnalysisResult(null);

        try {
            const result = await analyzeDocument(documentText, language);
            setAnalysisResult(result);
        } catch (err) {
            setError(t('docs.error'));
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div>
            <h3 className="font-semibold text-lg text-gray-700">{t('docs.title')}</h3>
            <p className="text-gray-500 text-sm mb-4">{t('docs.prompt')}</p>
            
            <textarea
                value={documentText}
                onChange={(e) => setDocumentText(e.target.value)}
                rows={10}
                className="w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 transition-shadow"
                placeholder={t('docs.placeholder')}
                disabled={isLoading}
            />

            <button
                onClick={handleAnalyze}
                disabled={isLoading || !documentText.trim()}
                className="mt-4 flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition-colors shadow disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <SparklesIcon className="w-5 h-5" />
                {isLoading ? t('docs.loading') : t('docs.button')}
            </button>

            {error && <p className="mt-4 text-red-600 bg-red-50 p-3 rounded-md">{error}</p>}

            {analysisResult && (
                <div className="mt-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <h3 className="text-xl font-bold text-gray-800 mb-4">{t('docs.results_title')}</h3>

                    <ResultSection title={t('docs.summary')}>
                        <p className="text-gray-700 whitespace-pre-wrap">{analysisResult.summary}</p>
                    </ResultSection>

                    <ResultSection title={t('docs.entities')}>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <strong className="text-gray-600">{t('docs.people')}:</strong>
                                <ul className="list-disc ps-5 mt-1 space-y-1">
                                    {analysisResult.entities.people.map((p, i) => <li key={i}>{p}</li>)}
                                </ul>
                            </div>
                             <div>
                                <strong className="text-gray-600">{t('docs.dates')}:</strong>
                                <ul className="list-disc ps-5 mt-1 space-y-1">
                                    {analysisResult.entities.dates.map((d, i) => <li key={i}>{d}</li>)}
                                </ul>
                            </div>
                             <div>
                                <strong className="text-gray-600">{t('docs.locations')}:</strong>
                                <ul className="list-disc ps-5 mt-1 space-y-1">
                                    {analysisResult.entities.locations.map((l, i) => <li key={i}>{l}</li>)}
                                </ul>
                            </div>
                        </div>
                    </ResultSection>

                     <ResultSection title={t('docs.arguments')}>
                        <ul className="list-disc ps-5 mt-1 space-y-2 text-gray-700">
                             {analysisResult.potential_arguments.map((arg, i) => <li key={i}>{arg}</li>)}
                        </ul>
                    </ResultSection>
                </div>
            )}
        </div>
    );
};

export default DocumentAnalyzer;
