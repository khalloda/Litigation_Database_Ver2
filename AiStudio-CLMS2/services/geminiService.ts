import { GoogleGenAI, Type } from "@google/genai";
import type { Case, DocumentAnalysisResult, Language } from '../types';

const API_KEY = process.env.API_KEY;

if (!API_KEY) {
  console.warn("API_KEY environment variable not set. AI features will be disabled.");
}

const ai = new GoogleGenAI({ apiKey: API_KEY! });

export async function generateCaseSummary(caseDetails: Case, language: Language): Promise<string> {
  if (!API_KEY) {
    // Simulate a delay and return a mock summary if API key is not available
    const title = language === 'ar' ? caseDetails.case_name_ar : caseDetails.case_name_en;
    return new Promise(resolve => {
      setTimeout(() => {
        if (language === 'ar') {
            resolve(`هذا ملخص تجريبي للقضية رقم ${caseDetails.case_number}: ${title}. جوهر هذه القضية يتعلق بـ ${caseDetails.case_description}. القضية حاليًا ${caseDetails.status}.`);
        } else {
            resolve(`This is a mock AI summary for case #${caseDetails.case_number}: ${title}. The core of this case involves ${caseDetails.case_description}. The case is currently ${caseDetails.status}.`);
        }
      }, 1500);
    });
  }

  const model = "gemini-2.5-flash";
  
  const clientName = language === 'ar' 
    ? (caseDetails.client.client_name_ar || caseDetails.client.client_name_en) 
    : (caseDetails.client.client_name_en || caseDetails.client.client_name_ar);

  const opponentsText = caseDetails.opponents.map(o => 
    language === 'ar' 
    ? (o.opponent_name_ar || o.opponent_name_en) 
    : (o.opponent_name_en || o.opponent_name_ar)
  ).join(', ');

  const attorneyName = language === 'ar' ? caseDetails.partner.lawyer_name_ar : caseDetails.partner.lawyer_name_en;
  const caseTitle = language === 'ar' ? caseDetails.case_name_ar : caseDetails.case_name_en;

  const prompt = `
    You are a paralegal assistant. Summarize the following legal case details into a concise, easy-to-read paragraph.
    The summary should be in ${language === 'ar' ? 'Arabic' : 'English'}.
    Focus on the key issue, the parties involved, and the current status.

    Case Title: ${caseTitle}
    Case Number: ${caseDetails.case_number}
    Description: ${caseDetails.case_description}
    Status: ${caseDetails.status}
    Client: ${clientName}
    Opponent(s): ${opponentsText}
    Attorney: ${attorneyName}

    Generate the summary now.
  `;

  try {
    const response = await ai.models.generateContent({
      model,
      contents: prompt,
    });
    return response.text;
  } catch (error) {
    console.error("Error generating case summary:", error);
    throw new Error("Failed to communicate with the AI model.");
  }
}

export async function analyzeDocument(documentText: string, language: Language): Promise<DocumentAnalysisResult> {
    if (!API_KEY) {
        // Simulate API call for document analysis
        return new Promise(resolve => setTimeout(() => resolve({
            summary: language === 'ar' ? 'هذا هو ملخص وهمي للمستند المقدم.' : 'This is a mock summary of the provided document.',
            entities: {
                people: language === 'ar' ? ['جون دو', 'جين سميث'] : ['John Doe', 'Jane Smith'],
                dates: ['2023-01-01', '2024-03-15'],
                locations: language === 'ar' ? ['قاعة المحكمة أ', 'المكتب الرئيسي'] : ['Courthouse A', 'Main Office']
            },
            potential_arguments: language === 'ar' ? ['انتهاك العقد', 'الإهمال'] : ['Breach of contract', 'Negligence claim']
        }), 2000));
    }

    const model = "gemini-2.5-flash";

    const prompt = `
        You are an expert paralegal AI. Analyze the following legal document text and provide a structured JSON response.
        The response should be in ${language === 'ar' ? 'Arabic' : 'English'}.
        The document text is:
        ---
        ${documentText}
        ---
        Extract the summary, key entities (people, dates, locations), and potential legal arguments.
    `;

    const schema = {
        type: Type.OBJECT,
        properties: {
            summary: { type: Type.STRING, description: 'A concise summary of the document.' },
            entities: {
                type: Type.OBJECT,
                properties: {
                    people: { type: Type.ARRAY, items: { type: Type.STRING }, description: 'Names of people mentioned.' },
                    dates: { type: Type.ARRAY, items: { type: Type.STRING }, description: 'Key dates mentioned.' },
                    locations: { type: Type.ARRAY, items: { type: Type.STRING }, description: 'Locations or places mentioned.' },
                },
                 required: ['people', 'dates', 'locations'],
            },
            potential_arguments: {
                type: Type.ARRAY,
                items: { type: Type.STRING },
                description: 'Potential legal arguments, claims, or defenses found in the text.',
            },
        },
        required: ['summary', 'entities', 'potential_arguments'],
    };

    try {
        const response = await ai.models.generateContent({
            model,
            contents: prompt,
            config: {
                responseMimeType: "application/json",
                responseSchema: schema,
            },
        });

        const jsonText = response.text.trim();
        return JSON.parse(jsonText) as DocumentAnalysisResult;
    } catch (error) {
        console.error("Error analyzing document:", error);
        throw new Error("Failed to communicate with the AI model for document analysis.");
    }
}