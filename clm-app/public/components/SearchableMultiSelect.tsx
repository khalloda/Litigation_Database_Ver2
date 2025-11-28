
import React, { useState, useRef, useEffect } from 'react';
import { useI18n } from '../hooks/useI18n';
import { XIcon, ChevronDownIcon } from './icons';

interface Option {
    value: string | number;
    label: string;
}

interface SearchableMultiSelectProps {
    options: Option[];
    selected: (string | number)[];
    onChange: (selected: (string | number)[]) => void;
    placeholder: string;
}

const SearchableMultiSelect: React.FC<SearchableMultiSelectProps> = ({ options, selected, onChange, placeholder }) => {
    const { t } = useI18n();
    const [isOpen, setIsOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const wrapperRef = useRef<HTMLDivElement>(null);

    const selectedOptions = options.filter(opt => selected.includes(opt.value));
    const unselectedOptions = options.filter(opt => !selected.includes(opt.value));

    const filteredOptions = unselectedOptions.filter(option =>
        option.label.toLowerCase().includes(searchTerm.toLowerCase())
    );

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);
    
    const handleToggleOption = (value: string | number) => {
        const newSelected = selected.includes(value)
            ? selected.filter(v => v !== value)
            : [...selected, value];
        onChange(newSelected);
    };

    const handleRemoveOption = (value: string | number) => {
        onChange(selected.filter(v => v !== value));
    };

    return (
        <div className="relative" ref={wrapperRef}>
            <div 
                className="w-full p-1 border border-gray-300 rounded-md bg-white flex flex-wrap items-center cursor-pointer min-h-[42px]"
                onClick={() => setIsOpen(!isOpen)}
            >
                <div className="flex-grow flex flex-wrap items-center gap-1 p-1">
                    {selectedOptions.length > 0 ? selectedOptions.map(opt => (
                        <span key={opt.value} className="flex items-center gap-1 bg-primary-100 text-primary-800 text-sm font-medium px-2 py-1 rounded-full">
                            {opt.label}
                            <button
                                type="button"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleRemoveOption(opt.value);
                                }}
                                className="text-primary-600 hover:text-primary-800"
                            >
                                <XIcon className="w-3 h-3" />
                            </button>
                        </span>
                    )) : <span className="text-gray-500 px-2">{placeholder}</span>}
                </div>
                <ChevronDownIcon className={`w-5 h-5 text-gray-400 transition-transform m-2 ${isOpen ? 'rotate-180' : ''}`} />
            </div>

            {isOpen && (
                <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                    <input
                        type="text"
                        className="w-full p-2 border-b outline-none"
                        placeholder={t('app.search_placeholder_generic')}
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        autoFocus
                    />
                    <ul className="max-h-60 overflow-auto">
                        {filteredOptions.length > 0 ? (
                            filteredOptions.map(option => (
                                <li
                                    key={option.value}
                                    className="px-3 py-2 cursor-pointer hover:bg-primary-100"
                                    onClick={() => handleToggleOption(option.value)}
                                >
                                    {option.label}
                                </li>
                            ))
                        ) : (
                            <li className="px-3 py-2 text-gray-500">{t('dashboard.no_cases_found')}</li>
                        )}
                    </ul>
                </div>
            )}
        </div>
    );
};

export default SearchableMultiSelect;
