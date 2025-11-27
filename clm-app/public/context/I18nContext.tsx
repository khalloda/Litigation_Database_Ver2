import React, { createContext, useState, useEffect, useCallback, ReactNode } from 'react';
import type { Language, LanguageDirection, Translations } from '../types';

interface I18nContextType {
  language: Language;
  setLanguage: (language: Language) => void;
  direction: LanguageDirection;
  t: (key: string) => string;
}

export const I18nContext = createContext<I18nContextType | undefined>(undefined);

export const I18nProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [language, setLanguage] = useState<Language>('en');
  const [direction, setDirection] = useState<LanguageDirection>('ltr');
  const [translations, setTranslations] = useState<Translations>({});
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    const newDirection = language === 'ar' ? 'rtl' : 'ltr';
    setDirection(newDirection);
    document.documentElement.lang = language;
    document.documentElement.dir = newDirection;

    const fetchTranslations = async () => {
      try {
        // Fetch the translation file from the public path.
        const response = await fetch(`/locales/${language}.json`);
        if (!response.ok) {
          throw new Error(`Could not load translations for ${language}`);
        }
        const data = await response.json();
        setTranslations(data);
        if (!isLoaded) {
          setIsLoaded(true);
        }
      } catch (error) {
        console.error("Translation file error:", error);
        // In case of error, you might want to default to a known language or show an error UI.
      }
    };

    fetchTranslations();
  }, [language, isLoaded]);

  const t = useCallback((key: string): string => {
    const keys = key.split('.');
    let result: string | Translations | undefined = translations;
    for (const k of keys) {
      if (typeof result === 'object' && result !== null && k in result) {
        result = result[k] as string | Translations;
      } else {
        return key; // Return key if translation not found
      }
    }
    return typeof result === 'string' ? result : key;
  }, [translations]);
  
  // Prevent rendering the rest of the app until initial translations are loaded.
  if (!isLoaded) {
    return null; // Or a loading spinner
  }

  return (
    <I18nContext.Provider value={{ language, setLanguage, direction, t }}>
      {children}
    </I18nContext.Provider>
  );
};
