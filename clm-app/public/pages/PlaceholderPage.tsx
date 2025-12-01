import React from 'react';
import { useI18n } from '../hooks/useI18n';

interface PlaceholderPageProps {
  title: string;
}

const PlaceholderPage: React.FC<PlaceholderPageProps> = ({ title }) => {
  const { t } = useI18n();
  return (
    <div className="container mx-auto">
      <h1 className="text-3xl font-bold text-gray-800 mb-6">{title}</h1>
      <div className="bg-white p-10 rounded-lg shadow-sm text-center">
        <p className="text-gray-600">{t('common.wip')}</p>
      </div>
    </div>
  );
};

export default PlaceholderPage;