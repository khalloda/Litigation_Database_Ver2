import React, { useState, useEffect, useRef } from 'react';
import { ChevronDownIcon } from './icons';

interface Option {
    value: string | number;
    label: string;
}

interface SearchableSelectProps {
    options: Option[];
    value: string | number;
    onChange: (value: string | number) => void;
    placeholder: string;
}

const SearchableSelect: React.FC<SearchableSelectProps> = ({ options, value, onChange, placeholder }) => {
    const [isOpen, setIsOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [highlightedIndex, setHighlightedIndex] = useState(-1);
    const wrapperRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    const selectedOption = options.find(option => option.value === value);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [wrapperRef]);
    
    useEffect(() => {
      if (isOpen) {
        setHighlightedIndex(-1);
      } else {
        setSearchTerm('');
      }
    }, [isOpen]);

    const filteredOptions = options.filter(option =>
        option.label.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    const handleSelectOption = (option: Option) => {
        onChange(option.value);
        setIsOpen(false);
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setHighlightedIndex(prev => (prev < filteredOptions.length - 1 ? prev + 1 : prev));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setHighlightedIndex(prev => (prev > 0 ? prev - 1 : 0));
        } else if (e.key === 'Enter' && highlightedIndex >= 0) {
            e.preventDefault();
            handleSelectOption(filteredOptions[highlightedIndex]);
        } else if (e.key === 'Escape') {
            setIsOpen(false);
        }
    };
    
    const toggleOpen = () => {
      setIsOpen(!isOpen);
      if (!isOpen) {
        setTimeout(() => inputRef.current?.focus(), 0);
      }
    }

    return (
        <div className="relative" ref={wrapperRef}>
             <div 
                className="w-full p-2 border border-gray-300 rounded-md shadow-sm bg-white flex justify-between items-center cursor-pointer"
                onClick={toggleOpen}
                tabIndex={0}
                onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') toggleOpen(); }}
            >
                 {isOpen ? (
                    <input
                        ref={inputRef}
                        type="text"
                        className="w-full bg-transparent outline-none"
                        placeholder="Search..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        onKeyDown={handleKeyDown}
                    />
                 ) : (
                    <span className={selectedOption ? 'text-gray-900' : 'text-gray-500'}>
                        {selectedOption ? selectedOption.label : placeholder}
                    </span>
                 )}
                <ChevronDownIcon className={`w-5 h-5 text-gray-400 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
            </div>

            {isOpen && (
                <ul className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    {filteredOptions.length > 0 ? (
                        filteredOptions.map((option, index) => (
                            <li
                                key={option.value}
                                className={`px-3 py-2 cursor-pointer hover:bg-primary-100 ${index === highlightedIndex ? 'bg-primary-100' : ''}`}
                                onClick={() => handleSelectOption(option)}
                                onMouseEnter={() => setHighlightedIndex(index)}
                            >
                                {option.label}
                            </li>
                        ))
                    ) : (
                         <li className="px-3 py-2 text-gray-500">No options found</li>
                    )}
                </ul>
            )}
        </div>
    );
};

export default SearchableSelect;