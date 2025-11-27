import api from './api';
import type { DocumentAnalysisResult, Language } from '../types';

export async function generateCaseSummary(
  caseId: number | string,
  language: Language
): Promise<string> {
  const response = await api.post('/ai/case-summary', {
    case_id: caseId,
    language,
  });
  return response.data.summary;
}

export async function analyzeDocument(
  documentText: string,
  language: Language
): Promise<DocumentAnalysisResult> {
  const response = await api.post('/ai/analyze-document', {
    text: documentText,
    language,
  });
  return response.data;
}

